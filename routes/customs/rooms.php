<?php

use App\Http\Controllers\Rooms\HotelRoomsApis;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "v1", "middleware" => ["auth:sanctum", "admin"]], function () {
	/**
	 * @todo Custome endpoints
	 */
	Route::group(["prefix" => "rooms", "controller" => HotelRoomsApis::class], function () {


		/**
		 * @todo Update room image
		 * @api /api/v1/rooms/image/:id
		 */
		Route::post("image/{id}", "updateImage");
		/**
		 * @todo Update room images
		 * @api /api/v1/rooms/images/:id
		 */
		Route::post("images/{id}", "updateImages");
		/**
		 * @todo Toggle room status
		 * @api /api/v1/rooms/status/:id
		 */
		Route::put("status/{id}", "toggleStatus");
	});
	/**
	 * @todo Api resources
	 * @api /api/v1/rooms
	 */
	Route::apiResource("rooms", HotelRoomsApis::class)->only(["store", "destroy", "update"]);
	Route::apiResource("rooms", HotelRoomsApis::class)
		->except(["store", "destroy", "update"])
		->withoutMiddleware(["admin", 'auth:sanctum']);
});
