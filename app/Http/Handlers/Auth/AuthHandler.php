<?php namespace App\Http\Handlers\Auth;

use App\Enums\Response\ResCodes;
use App\Enums\Response\ResMessages;
use App\Enums\UsersTypes;
use App\Http\Handlers\Core\BaseHandler;
use App\Http\Modules\Modules;
use App\Jobs\AccountRecoveryJob;
use App\Jobs\AccountVerificationJob;
use App\Mail\AccountVerificationMail;
use App\Http\Handlers\Users\UserHelpers;
use App\Mail\AccountRecoveryMail;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthHandler
{
	use BaseHandler, UserHelpers;

	public function createUser(string $userType)
	{
		try {
			$params = $this->request->all(["email", "password"]);

			// assign the user role base on provided value
			$params["role"] = $userType;

			$responseData = DB::transaction(function () use ($params) {
				/* create user  */
				if (!($User = Modules::User()->create($params))) {
					throw new Exception("Could not create a new user account.");
				}

				/* create verification otp */
				if (!($Code = Modules::OTP()->create(["account_id" => $User->account_id, "sent_to" => $User->email]))) {
					throw new Exception("Unable to generate account verification OTP.");
				}

				/* Send user email verification */
				Mail::to($User)->send(new AccountVerificationMail($Code->code));

				return $User;
			}, attempts: 1);

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Account created successfully, we've sent you a verification code to your email.";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 201;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function resendUserVerificatonCode(string $id)
	{
		try {
			$params = $this->request->all([""]);

			$responseData = DB::transaction(function () use ($params, $id) {
				if (!($User = Modules::User()->get($id))) {
					throw new Exception("The provided user account was not found.");
				}

				if (!($otp = Modules::OTP()->create(["account_id" => $User->account_id, "sent_to" => $User->email]))) {
					throw new Exception("Unable to generate account verification OTP.");
				}

				/* Resend user verification code*/
				Mail::to($User)->send(new AccountVerificationMail($otp->code));
				return null;
			}, attempts: 1);

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Success, a new verification has been sent to your email.";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 204;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function verifyUserAccount()
	{
		try {
			$params = $this->request->all(["code"]);

			if (!Modules::OTP()->isValid($params["code"])) {
				return $this->raise("Invalid account verification code.", null, 402);
			}

			$responseData = DB::transaction(function () use ($params) {
				$OTP = Modules::OTP()->get($params["code"]);

				$updateUser["is_verified"] = true;
				if (!Modules::User()->update($OTP->account_id, $updateUser)) {
					throw new Exception("Unable to process account verification.");
				}

				Modules::OTP()->invalidate($params["code"]);

				$user = $this->getUser($OTP->account_id);
				$user->api_token = $user->access_token;
				return $user;
			}, attempts: 1);

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Your account has been verified successfully!";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 200;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	// -------------------> {Account Recovery}

	public function recoverAccount()
	{
		try {
			$params = $this->request->all(["email"]);

			$responseData = DB::transaction(function () use ($params) {
				if (!($User = Modules::User()->get($params["email"]))) {
					throw new Exception("The provided user email was not found.");
				}

				if (!($OTP = Modules::OTP()->create(["account_id" => $User->account_id, "sent_to" => $User->email]))) {
					throw new Exception("Could not generate account recovery email.");
				}

				// Send user verification code.
				Mail::to($User)->send(new AccountRecoveryMail($OTP->code));

				return $User;
			}, attempts: 1);

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "You've received a confirmation code from us. We kindly request you to verify your identity by confirming that it's truly you.";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 200;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function resendConfirmationCode(string $id)
	{
		try {
			$params = $this->request->all([""]);

			$responseData = DB::transaction(function () use ($params, $id) {
				if (!($User = Modules::User()->get($id))) {
					return $this->raise("The provided user was not found.", null, 422);
				}

				if (!($OTP = Modules::OTP()->create(["account_id" => $User->account_id, "sent_to" => $User->email]))) {
					return throw new Exception("Unable to generate account recovery code.");
				}

				// Send user verification code.
				Mail::to($User)->send(new AccountRecoveryMail($OTP->code));

				return null;
			}, attempts: 1);

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Success, a new confirmation code has been sent to you.";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = ResCodes::VOID->value;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function confirmPasswordReset()
	{
		try {
			$params = $this->request->all(["code"]);

			$responseData = DB::transaction(function () use ($params) {
				if (!Modules::OTP()->isValid($params["code"])) {
					return $this->raise(ResMessages::OTP_EXP->value, null, ResCodes::CLIENT_ERR->value);
				}

				$OTP = Modules::OTP()->get($params["code"]);

				/* generate a password reset token  */
				$resetToken = Crypt::encryptString(
					json_encode([
						"account_id" => $OTP->account_id,
						"timer" => Carbon::now()
							->addMinutes(15)
							->toISOString(true),
					])
				);

				Modules::OTP()->invalidate($params["code"]);

				return ["reset_token" => $resetToken];
			}, attempts: 1);

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Thank you, your identity has been confirmed. You can now reset your password.";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = ResCodes::OK->value;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function resetAccountPassword()
	{
		try {
			$params = $this->request->all(["reset_token", "password"]);

			/* decrypt the reset token  */
			if (!($token = Crypt::decryptString($params["reset_token"]))) {
				return $this->raise(ResMessages::ENCRYPT_ERR->value, null, ResCodes::CLIENT_ERR->value);
			}

			$tokenData = json_decode($token);

			// validate timer
			$now = Carbon::now();
			$timer = Carbon::createFromDate($tokenData->timer);
			if (!$now > $timer) {
				return $this->raise(ResMessages::PASSWORD_RESET_EXP->value, null, ResCodes::CLIENT_ERR->value);
			}

			$responseData = DB::transaction(function () use ($params, $tokenData) {
				if (!Modules::User()->update($tokenData->account_id, ["password" => $params["password"]])) {
					throw new Exception(ResMessages::DB_ERR->value);
				}

				if (!Modules::User()->resetAccessToken($tokenData->account_id)) {
					throw new Exception(ResMessages::DB_ERR->value);
				}

				return null;
			}, attempts: 1);

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Your account password has been reset successfully.";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 200;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	// ------------------> {Authorization}

	public function usersAccountAuthorization()
	{
		try {
			$params = $this->request->all(["email", "password"]);

			if (!($User = Modules::User()->get($params["email"]))) {
				return $this->raise(ResMessages::DB_ERR->value, null, ResCodes::DB_ERR->value);
			}

			if (!Auth::attempt($params)) {
				return $this->raise(ResMessages::INCOR_CREDENTIALS->value, null, ResCodes::UNAUTHORIZED->value);
			}

			/* make sure user has verify their account. */
			if (!$User->is_verified) {
				$this->resendUserVerificatonCode($User->account_id);
			}

			/* The client should check the `verified_at` property to redirect user to verification screen and enter onboarding flow */
			$user = $this->getUser($User->account_id);
			$user->api_token = $user->access_token;
			$responseData = $user;

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Account access authorized.";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = ResCodes::OK->value;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}
}