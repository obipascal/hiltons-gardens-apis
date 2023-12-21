<?php namespace App\Http\Handlers\Payments;

use App\Http\Modules\Modules;
use App\Models\Account\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Paystack;

use function App\Utilities\getReference;

trait PaymentMethodHandler
{
	public function addPaymenthMethod()
	{
		try {
			$params = $this->request->all(["amount"]);

			/** @var User */
			$user = $this->request->user();

			// Start: Update the amount to N100
			$params["amount"] = 100;

			// Step 1: Initiate a charge of N100, which will be refunded later after card has been used.
			$serviceParams["metadata"]["todo"] = "addPaymentMethod";

			$reference = getReference("HLG");

			// Iniate paystack request to create the transaction link, that user will
			//  be redirected to so he/she can complete payment that way we can obtain the resuable token.
			$service = Paystack::Transaction()->createLink($params["amount"], $user->email, "NGN", $reference, $serviceParams);

			if (!$service->success) {
				return $this->raise("Sorry we're unable to complete this transaction.");
			}

			// Step 2: Save the transaction reference and return link to user for payment
			$responseData = DB::transaction(function () use ($params, $user, $reference, $service) {
				$payMethodParams = [
					"account_id" => $user->account_id,
					"reference" => $reference,
					"status" => "inactive",
				];

				if (!($payMethod = Modules::Payments()->createPayMethod($payMethodParams))) {
					throw new Exception("Unable to complete database operation.");
				}

				return [
					"charge" => $payMethod,
					"auth_link" => $service->response->data->authorization_url,
				];
			}, attempts: 1);

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Please, complete this transaction to add your payment method. Thank you!";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 201;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function fetchPaymentMethod(string $id)
	{
		try {
			if (!($responseData = Modules::Payments()->getPayMethod($id))) {
				return $this->riase("Unable to retrieve payment method.");
			}

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Payment method retrieved.";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 200;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function fetchUserPaymentMethods()
	{
		try {
			$params = $this->request->all([""]);
			// Get user

			/** @var User */
			$user = $this->request->user();

			if (!($responseData = Modules::Payments()->allPayMethods($user->account_id))) {
				return $this->raise("Could not retrieve user payment methods.");
			}

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

	public function deletePaymentMethod(string $id)
	{
		try {
			if (!($responseData = Modules::Payments()->deletePayMethod($id))) {
				return $this->riase("Unable to delete payment method.");
			}

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Payment method retrieved.";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 204;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}
}
