<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Config\RESTResponse;
use App\Http\Controllers\Controller;
use App\Http\Handlers\Handlers;
use App\Jobs\PSWebhookJob;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhooksApis extends Controller
{
	use RESTResponse;

	/**
	 * Verify wallet topup transaction
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		try {
			$payload = $request->all();

			Log::alert("WBH", [$payload]);

			$event = $payload["event"];
			switch ($event) {
				case "charge.success":
					Handlers::Webhook($request)->activateCard($payload);
					break;

				default:
					# code...
					break;
			}

			/* Finally all went well return the response to caller/client */
			return $this->sendResponse(null, "Event recieved.");
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["file" => $th->getFile(), "line" => $th->getLine()]);

			return $this->terminateRequest("ERROR", $this->RESPONSE ?? $th->getMessage(), 500);
		}
	}
}
