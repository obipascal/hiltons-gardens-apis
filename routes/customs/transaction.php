<?php

use App\Http\Controllers\Payments\TransactionApi;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "v1"], function () {
	/**
	 * @todo Api resources
	 * @api /api/v1/transactions
	 */
	Route::apiResource("transactions", TransactionApi::class)->only(["show"]);
});
