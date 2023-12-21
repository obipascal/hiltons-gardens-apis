<?php

use App\Http\Controllers\Payments\PaymentMethodsApis;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "v1", "middleware" => ["auth:sanctum"]], function () {
	/**
	 * @todo Payment methods
	 * @api /api/v1/payment_methods
	 */
	Route::apiResource("payment_methods", PaymentMethodsApis::class);
});