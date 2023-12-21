<?php namespace App\Http\Handlers\Webhooks;

use App\Http\Handlers\Core\BaseHandler;
use App\Http\Modules\Modules;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
}
