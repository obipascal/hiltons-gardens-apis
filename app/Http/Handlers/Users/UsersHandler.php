<?php namespace App\Http\Handlers\Users;

use App\Http\Handlers\Core\BaseHandler;
use App\Http\Modules\Modules;
use App\Jobs\AccountVerificationJob;
use App\Enums\Response\ResCodes;
use App\Enums\Response\ResMessages;
use App\Models\Users\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UsersHandler
{
	use BaseHandler, UserHelpers;

	public function updateAccount(string $id)
	{
		try {
			DB::beginTransaction();

			if (!($User = Modules::User()->get($id))) {
				return $this->raise(ResMessages::DB_ERR->value, null, ResCodes::DB_ERR->value);
			}

			$params = $this->request->all(["first_name", "last_name", "email", "phone_number"]);

			foreach ($params as $param => $value) {
				if (empty($value)) {
					unset($params[$param]);
				}
			}

			if (!Modules::User()->update($User->account_id, $params)) {
				return $this->raise(ResMessages::DB_ERR->value, null, ResCodes::DB_ERR->value);
			}

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Success, profile updated successfully!";
			$response["type"] = "account";
			$response["body"] = $this->getUser($User->account_id, false);
			$responseCode = ResCodes::OK->value;

			DB::commit();

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			DB::rollBack();
			DB::commit();

			return $this->raise();
		}
	}

	public function fetchUser()
	{
		try {
			/** @var User */
			$User = $this->request->user();

			$User->api_token = $User->access_token;
			$User->wallet;
			$User->security;
			$User->banking_details;

			$responseData = $User;

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Success, user retrieved";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 200;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}
}
