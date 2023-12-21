<?php

use App\Http\Controllers\Bookings\BookingApis;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "v1", "middleware" => ["auth:sanctum"]], function () {
	/**
	 * @todo Bookings Apis
	 * @api /api/v1/bookings
	 */
	Route::apiResource("bookings", BookingApis::class);
});
