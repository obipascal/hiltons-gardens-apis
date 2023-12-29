<?php namespace App\Http\Handlers\Payments;

use App\Http\Handlers\Core\BaseHandler;
use App\Http\Handlers\Payments\PaymentMethodHandler;
use App\Http\Modules\Modules;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentHandler
{
	use BaseHandler, PaymentMethodHandler;

	public function fetchTransaction(string $reference_or_id)
	{
		try {
			$params = $this->request->all([""]);

			// get the booking
			if (!($transaction = Modules::Payments()->get($reference_or_id))) {
				return $this->raise("Sorry transaction was not found", null, 404);
			}

			// pull the booking relationship and then, room relation from booking
			$transaction->booking;
			$transaction->booking->room;

			$responseData = $transaction;

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Transaction retrieved";
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
