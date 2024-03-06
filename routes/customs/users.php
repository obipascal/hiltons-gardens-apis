<?php

use App\Http\Controllers\Users\UsersApi;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "v1", "middleware" => ["auth:sanctum"]], function () {
	/**
	 * @todo User api
	 * @api /api/v1/users
	 */
	Route::group(["controller" => UsersApi::class, "prefix" => "users"], function () {
		/**
		 * @todo UPdate User profile
		 * @api /api/v1/users
		 */
		Route::put("/", "update");
        /**
         * @todo Handle user account deletion
         * @api /api/v1/users/delete
         */
        Route::post("delete", "store")->withoutMiddleware('auth:sanctum');

	});
	Route::apiResource("users", UsersApi::class)->except(["update", 'store']);
});
