<?php

use App\Http\Controllers\Rooms\HotelRoomsApis;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "v1", "middleware" => ["auth:sanctum", "admin"]], function () {
	/**
	 * @todo Api resources
	 * @api /api/v1/rooms
	 */
	Route::apiResource("rooms", HotelRoomsApis::class)->only(["store", "destroy", "update"]);
	Route::apiResource("rooms", HotelRoomsApis::class)
		->except(["store", "destroy", "update"])
		->withoutMiddleware("admin");
});