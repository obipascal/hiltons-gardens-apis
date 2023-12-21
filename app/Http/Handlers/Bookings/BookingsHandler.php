<?php namespace App\Http\Handlers\Bookings;

use App\Http\Handlers\Core\BaseHandler;
use App\Http\Modules\Modules;
use App\Models\Account\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Paystack;

use function App\Utilities\getReference;

class BookingsHandler
{
	use BaseHandler;

	public function bookRoom()
	{
		try {
			// Get reuest params
			$params = $this->request->all(["room_id", "checkin", "checkout", "num_rooms"]);

			// check if the room is bookable
			if (!Modules::Room()->canBook($params["room_id"])) {
				return $this->raise("Sorry this room cannot be booked at the moment.");
			}

			// Validate booking check in and out timing.
			$checkin = Carbon::createFromDate($params["checkin"]);
			$checkout = Carbon::createFromDate($params["checkout"]);

			if (!($checkout > $checkin)) {
				return $this->raise("Sorry check-in date/time must be ahead of check-out date/time.");
			}

			$responseData = DB::transaction(function () use ($params, $checkin, $checkout) {
				// Obtain the room
				$room = Modules::Room()->get($params["room_id"]);

				// Obtain the active login user
				/** @var User */
				$user = $this->request->user();

				// generate a transaction reference
				$reference = getReference("HLG");

				// Get the number of day's from date
				$bookingIntervals = $checkin->diff($checkout);
				$intervalInDays = $bookingIntervals->days;

				// Prepare booking details
				$bookingParams = [...$params, "duration" => $intervalInDays, "account_id" => $user->account_id];

				// Create booking
				if (!($booking = Modules::Bookings()->create($bookingParams))) {
					throw new Exception("Unable to create booking.");
				}

				// Get and calculate the booking amount
				$amount = $room->price;
				$subtotal = $amount * $booking->num_rooms * $booking->duration;

				// Prepare transaction details
				$transParams = [
					"reference" => $reference,
					"booking_id" => $booking->booking_id,
					"amount" => $amount,
					"subtotal" => $subtotal,
					"total" => $subtotal,
				];

				// Create transaction
				if (!($trans = Modules::Payments()->create($transParams))) {
					throw new Exception("Unable to create transaction.");
				}

				// Service request metadata
				$metadata["metadata"]["todo"] = "bookingCharge";

				// Check if user has their billing setup
				if (!empty($user->billing)) {
					// booking will be charged by linked card
					try {
						// Initiate services request to charge user connected card
						$service = Paystack::Transaction()->chargeAuth($trans->total, $user->email, $user->billing->auth_token, "NGN", $reference, $metadata);
						if (!$service->success) {
							throw new Exception("Transaction could not be completed with your linked card.", 505);
						}

						// get charge response
						$response = $service->response->data;

						// check if the charge was successful
						if (!$response->status === "success") {
							throw new Exception("Sorry we're unable to process your payment at the moment", 505);
						}

						// Reserve room
						if (!Modules::Room()->update($room->room_id, ["status" => "reserved"])) {
							throw new Exception("Could not update room status.");
						}

						// Update booking payment status
						if (!Modules::Payments()->update($trans->trans_id, ["status" => "success"])) {
							throw new Exception("Could not update transaction status.");
						}

						// Update booking status
						if (!Modules::Bookings()->update($booking->booking_id, ["status" => "active", "trans_id" => $trans->trans_id])) {
							throw new Exception("Payment completed, but unable to process booking status.");
						}

						/**
						 * @todo Send booking details via email
						 */

						//  Return the response data
						return [
							"transaction" => Modules::Payments()->get($trans->trans_id),
							"booking" => Modules::Bookings()->get($booking->booking_id),
							"authorization_link" => null,
						];
					} catch (Exception $th) {
						// Confirm error code to switch payment collection flow
						if ($th->getCode() == 505) {
							// Generate a payment link rather
							$service = Paystack::Transaction()->createLink($trans->total, $user->email, "NGN", $reference, $metadata);
							if (!$service->success) {
								throw new Exception("We couldn't process transaction at the moment.");
							}
							// return the payment link
							return [
								"transaction" => $trans,
								"booking" => $booking,
								"authorization_link" => $service->response->data->authorization_url,
							];
						}
					}
				} else {
					// booking will have to go through a checkout link flow
					// Generate a payment link rather
					$service = Paystack::Transaction()->createLink($trans->total, $user->email, "NGN", $reference, $metadata);
					if (!$service->success) {
						throw new Exception("We couldn't process transaction at the moment.");
					}
					// return the payment link
					return [
						"transaction" => $trans,
						"booking" => $booking,
						"authorization_link" => $service->response->data->authorization_url,
					];
				}

				return null;
			}, attempts: 1);

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Success";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 200;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}
}
