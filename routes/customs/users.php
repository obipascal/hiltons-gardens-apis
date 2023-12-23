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
	});
	Route::apiResource("users", UsersApi::class)->except(["update"]);
});
