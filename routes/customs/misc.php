<?php

use App\Http\Controllers\Misc\FavoritesApis;
use App\Http\Controllers\Misc\ReviewsApis;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "v1", "middleware" => ["auth:sanctum"]], function () {
	/**
	 * @todo Review Apis
	 * @api /api/v1/reviews
	 */
	Route::apiResource("reviews", ReviewsApis::class)->except(["update", "index", "destory"]);
	/**
	 * @todo Review Apis
	 * @api /api/v1/favorites
	 */
	Route::apiResource("favorites", FavoritesApis::class)->except(["update", "show"]);
});
