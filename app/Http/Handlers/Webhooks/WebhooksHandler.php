<?php namespace App\Http\Handlers\Webhooks;

use App\Http\Handlers\Core\BaseHandler;
use App\Http\Modules\Modules;
use App\Mail\BookingReservationDetailsMail;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Paystack;

class WebhooksHandler
{
	use BaseHandler;

	public function activateCard(array $payload)
	{
		try {
			$params = $this->request->all([""]);

			// Step 1: Get the payload data
			$data = $payload["data"];
			// Step 2: Get the meta data
			$metaData = $data["metadata"] ?? null;

			$responseData = DB::transaction(function () use ($params, $data, $metaData) {
				// Step 3: Check if the action is to add payment method
				if (!empty($metaData)) {
					if (isset($metaData["todo"])) {
						if ($metaData["todo"] === "addPaymentMethod") {
							// Step 4: If the action is to add payment method, Check if the transaction referece is valid
							if (Modules::Payments()->cardRefExists($data["reference"]) && $data["authorization"]["reusable"]) {
								// Step 5:Update the payent method status by confirming that the transaction was successful.
								$updateParams = match ($data["status"]) {
									"success" => [
										"status" => "active",
										"auth_token" => $data["authorization"]["authorization_code"],
										"bin" => $data["authorization"]["bin"],
										"last4" => $data["authorization"]["last4"],
										"exp_month" => $data["authorization"]["exp_month"],
										"exp_year" => $data["authorization"]["exp_year"],
										"card_type" => $data["authorization"]["card_type"],
										"bank" => $data["authorization"]["bank"],
										"card_name" => $data["authorization"]["account_name"],
									],
									default => ["status" => "inactive"],
								};

								// Update the payment method
								if (!Modules::Payments()->updatePayMethod($data["reference"], $updateParams)) {
									throw new Exception("Unable to update payment method.");
								}

								// make a refund of 100 back to user
								$service = Paystack::Transaction()->createRefunds($data["reference"]);
								if (!$service->success) {
									throw new Exception("Unable to process refund");
								}
							}
						}
					}
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

	public function confirmWebhookPayment(array $payload)
	{
		try {
			$params = $this->request->all([""]);

			// Get the payload data
			$data = $payload["data"];
			// Get the metadata
			$metadata = $data["metadata"];

			// Check if transaction exist
			if (!Modules::Payments()->exists($data["reference"])) {
				return $this->raise("Invalid transaction");
			}

			// Get transaction
			$trans = Modules::Payments()->get($data["reference"]);
			// Get booking
			$booking = Modules::Bookings()->get($trans->booking_id);
			// Get user
			$user = Modules::User()->get($booking->account_id);

			// Check if the transaction has already been handled
			if (Modules::Payments()->isSuccessful($data["reference"])) {
				return $this->raise("Transaction already handled.");
			}

			// Check if the payment was for booking charges
			if (!empty($metadata)) {
				if ($metadata["todo"] !== "bookingCharge") {
					return $this->raise("This handle can only handle booking charges.");
				}
			}

			// Check if the transaction was successful really
			if ($data["status"] !== "success") {
				return $this->raise("Sorry trannsaction was {$data["status"]}");
			}

			// Update transaction status
			if (!Modules::Payments()->update($trans->trans_id, ["status" => "success"])) {
				return $this->raise("Unable to update transaction status.");
			}

			// Update booking
			if (!Modules::Bookings()->update($booking->booking_id, ["status" => "active"])) {
				return $this->raise("Unable to update booking status.");
			}

			// Update room status
			if (!Modules::Room()->update($booking->room_id, ["status" => "reserved"])) {
				return $this->raise("Unable to reserve room");
			}

			/**
			 * @todo Send booking email
			 */
			// Get update booking and transaction objects
			$trans = Modules::Payments()->get($trans->trans_id);
			$booking = Modules::Bookings()->get($booking->booking_id);

			/**
			 * @todo Send booking details via email
			 */
			Mail::to($user)->send(new BookingReservationDetailsMail($user, $booking));

			$responseData = DB::transaction(function () use ($params, $user, $data, $metadata) {
				//  Check if user has billing setup otherwise, create one if setup update only when channel is card
				if (isset($data["authorization"])) {
					if ($data["authorization"]["channel"] === "card") {
						// When user already has existing card linked, swape previous card with newly selected card / used card
						if (!empty($user->billing)) {
							// Update billing details
							$updateParams = [
								"status" => "active",
								"auth_token" => $data["authorization"]["authorization_code"],
								"bin" => $data["authorization"]["bin"],
								"last4" => $data["authorization"]["last4"],
								"exp_month" => $data["authorization"]["exp_month"],
								"exp_year" => $data["authorization"]["exp_year"],
								"card_type" => $data["authorization"]["card_type"],
								"bank" => $data["authorization"]["bank"],
								"card_name" => $data["authorization"]["account_name"],
							];

							// Update the payment method
							if (!Modules::Payments()->updatePayMethod($user->billing->pay_method_id, $updateParams)) {
								throw new Exception("Unable to update payment method.");
							}
						}
						// Create / add card used as default payment method
						else {
							$payMethodParams = [
								"account_id" => $user->account_id,
								"reference" => $data["reference"],
								"status" => "inactive",
								"status" => "active",
								"auth_token" => $data["authorization"]["authorization_code"],
								"bin" => $data["authorization"]["bin"],
								"last4" => $data["authorization"]["last4"],
								"exp_month" => $data["authorization"]["exp_month"],
								"exp_year" => $data["authorization"]["exp_year"],
								"card_type" => $data["authorization"]["card_type"],
								"bank" => $data["authorization"]["bank"],
								"card_name" => $data["authorization"]["account_name"],
							];

							if (!($payMethod = Modules::Payments()->createPayMethod($payMethodParams))) {
								throw new Exception("Unable to complete database operation.");
							}

							return $payMethod;
						}
					}
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
