<?php

use App\Http\Controllers\Authentiction\UserAuthApis;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "v1", "controller" => UserAuthApis::class], function () {
	/**
	 * @todo Account creation
	 */
	Route::prefix("auth/create")->group(function () {
		/**
		 * @todo Create new user account
		 * @api /api/v1/auth/create/user
		 */
		Route::post("user", "createUserAccount");
		/**
		 * @todo Create new admin account
		 * @api /api/v1/auth/create/admin
		 */
		Route::post("admin", "createAdminAccount");
		/**
		 * @todo Resend account verification code
		 * @api /api/v1/auth/create/resend_code/:account_id
		 */
		Route::put("resend_code/{id}", "resendVerificationCode")->whereNumber("id");
		/**
		 * @todo Verify account
		 * @api /api/v1/auth/create/verify
		 */
		Route::post("verify", "verifyAccount");
	});

	/**
	 * @todo Account password reseting
	 */
	Route::prefix("auth/reset")->group(function () {
		/**
		 * @todo Recover account
		 * @api /api/v1/auth/reset
		 */
		Route::post("/", "accountRecovery");
		/**
		 * @todo Resend reset verification code
		 * @api /api/v1/auth/reset/resend_code/:account_id
		 */
		Route::put("resend_code/{id}", "resendPasswordResetVerificatoinCode")->whereNumber("id");
		/**
		 * @todo confirm account reset operation
		 * @api /api/v1/auth/reset/confirm
		 */
		Route::post("confirm", "confirmPasswordResetCode");
		/**
		 * @todo Reset account password
		 * @api /api/v1/auth/reset/password
		 */
		Route::post("password", "resetAccountPassword");
	});

	/**
	 * @todo Auth authorization
	 */
	Route::prefix("auth/authorize")->group(function () {
		/**
		 * @todo Authorize user account
		 * @api /api/v1/auth/authorize
		 */
		Route::post("/", "authorizeAccountAccess");
	});
});