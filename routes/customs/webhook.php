<?php

use App\Http\Controllers\Webhook\WebhooksApis;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "v1", "middleware" => ["paystack"]], function () {
	/**
	 * @todo webhook apis
	 * @api /api/v1/webhook
	 */
	Route::apiResource("webhook", WebhooksApis::class);
});