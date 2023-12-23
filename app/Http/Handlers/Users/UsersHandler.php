<?php namespace App\Http\Handlers\Users;

use App\Http\Handlers\Core\BaseHandler;
use App\Http\Modules\Modules;
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

	public function updateUser()
	{
		try {
			/** @var User */
			$User = $this->request->user();

			$params = $this->request->all(["first_name", "last_name", "billing_address", "phone_number"]);

			$responseData = DB::transaction(function () use ($params, $User) {
				foreach ($params as $param => $value) {
					if (empty($value)) {
						unset($params[$param]);
					}
				}

				if (!Modules::User()->update($User->account_id, $params)) {
					return $this->raise(ResMessages::DB_ERR->value, null, ResCodes::DB_ERR->value);
				}

				return Modules::User()->get($User->account_id);
			}, attempts: 1);

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "User profile updated.";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 200;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function fetchUser()
	{
		try {
			/** @var User */
			$User = $this->request->user();

			$User->api_token = $User->access_token;

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

	public function fetchUserByID(string $id)
	{
		try {
			$User = Modules::User()->get($id);

			$User->api_token = $User->access_token;

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
