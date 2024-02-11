<?php namespace App\Http\Handlers\Bookings;

use App\Http\Handlers\Core\BaseHandler;
use App\Http\Modules\Modules;
use App\Mail\BookingReservationDetailsMail;
use App\Models\Account\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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

			// swap dates params
			$params["checkin"] = $checkin->toDateTimeString();
			$params["checkout"] = $checkout->toDateTimeString();

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

				// Update booking with transaction Id
				Modules::Bookings()->update($booking->booking_id, ["trans_id" => $trans->trans_id]);

				// Service request metadata
				$metadata["metadata"]["todo"] = "bookingCharge";
				$metadata["callback_url"] = route("app-callback");

				// Check if user has their billing setup
				if (!empty($user->billing)) {
					// booking will be charged to linked card

					try {
						//  but first make sure its active
						if (!Modules::Payments()->payMethodIsValid($user->billing->pay_method_id)) {
							throw new Exception("Billing method is not activated yet.", 505);
						}

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

						// Get update booking and transaction objects
						$trans = Modules::Payments()->get($trans->trans_id);
						$booking = Modules::Bookings()->get($booking->booking_id);

						// Send booking details via email
						Mail::to($user)->send(new BookingReservationDetailsMail($user, $booking));

						//  Return the response data
						return [
							"transaction" => $trans,
							"booking" => $booking,
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

	public function fetchBookings()
	{
		try {
			$params = $this->request->all([""]);

			// Get the active auth user
			/** @var User */
			$user = $this->request->user();

			// Get number of records to return
			$perPage = $this->request->get("perPage") ?? 100;

			/** @var Collection */
			if (!($responseData = Modules::Bookings()->getUserBookings($user->account_id, $perPage))) {
				return $this->raise("Unable to retrieve user bookings.");
			}

			$responseData->each(function ($booking) {
				$booking->checkin_formatted = Carbon::createFromDate($booking->checkin)->toFormattedDayDateString();
				$booking->checkout_formatted = Carbon::createFromDate($booking->checkout)->toFormattedDayDateString();
			});

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "User bookings retrieved";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 200;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function fetchBooking(string $id)
	{
		try {
			$params = $this->request->all([""]);

			$responseData = Modules::Bookings()->get($id);

			$responseData->checkin_formatted = Carbon::createFromDate($responseData->checkin)->toFormattedDayDateString();
			$responseData->checkout_formatted = Carbon::createFromDate($responseData->checkout)->toFormattedDayDateString();
			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Booking retrieved";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 200;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function cancelBooking(string $id)
	{
		try {
			$params = $this->request->all([""]);

			// Get the booking
			$booking = Modules::Bookings()->get($id);

			// make sure booking is still active
			if ($booking->status !== "active") {
				return $this->raise("Sorry this booking has been {$booking->status}.");
			}

			// calculate the checkin and checkout date diff
			$checkinDate = Carbon::createFromDate($booking->checkin);
			$checkoutDate = Carbon::createFromDate($booking->checkout);
			$bookingDateInterv = $checkinDate->diff($checkoutDate);

			// Make sure the check-in time is at least 1 hour ahead of cancelation
			$now = Carbon::now();
			$checkin = Carbon::createFromDate($booking->checkin);
			$interval = $checkin->diff($now);

			// Now when their diff in days is greater than or equals 1, cancelation should
			// happen in less than a day to booking time otherwise 1 hour to booking time
			if ($bookingDateInterv->d > 1) {
				// If the cancelation date is the same as booking date terminate operation
				if ($checkinDate->isToday()) {
					return $this->raise("Booking can no longer be canceled.");
				}

				if (!($interval->d >= 1)) {
					return $this->raise("Sorry booking can only be canceled a day ahead of check-in date.");
				}
			} else {
				// When booking diff is hourly
				if (!($interval->h >= 1)) {
					return $this->raise("Sorry booking can only be canceled 1 hour ahead of check-in time.");
				}
			}

			// Initiate a refund
			$service = Paystack::Transaction()->createRefunds($booking->transaction->reference);
			if (!$service->success) {
				return $this->raise("Sorry we could not initiate a refund to your card or bank account.");
			}

			// Update booking status
			if (!Modules::Bookings()->update($id, ["status" => "canceled"])) {
				return $this->raise("Refund successful! But could not update booking.");
			}

			// Update transaction status
			if (!Modules::Payments()->update($booking->trans_id, ["status" => "refund"])) {
				return $this->raise("Refund successful! But could not update payment status.");
			}

			// Update room
			if (!Modules::Room()->update($booking->room_id, ["status" => "active"])) {
				return $this->raise("Refund successful! But could not reactivate room.");
			}

			$responseData = null;

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Booking has been successfully canceled.";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 204;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function expireBookings()
	{
		try {
			$params = $this->request->all([""]);

			// Fetch all bookings
			$bookings = Modules::Bookings()->getReservedBookings();
			if (!($bookings->count() > 0)) {
				return $this->raise("No bookings");
			}

			// Loop to validate expiration date and re-activate booking and room

			$bookings->each(function ($booking) {
				// get the time for today
				$today = Carbon::now();
				$checkoutDate = Carbon::createFromDate($booking->checkout);

				// expire booking if the checkout date is in the past
				if ($today > $checkoutDate) {
					// reactivate room
					Modules::Room()->update($booking->room_id, ["status", "active"]);

					// mark booking as completed
					Modules::Bookings()->update($booking->booking_id, ["status", "completed"]);
				}else {
                    Log::info("Booking is still active", ['HAS_EXPIRED' => $today > $checkoutDate, 'booking_id' => $booking->booking_id, 'checkout' => $booking->checkout, 'today' => $today, 'checkoutDate' => $checkoutDate]);
                }
			});

			$responseData = null;

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
