<?php namespace App\Http\Modules\Security;

use App\Http\Modules\Core\BaseModule;
use App\Models\Verification\OTP;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

use function App\Utilities\random_id;
use function App\Utilities\random_string;

class OTPModule
{
	use BaseModule;

	public function create($params): bool|null|OTP
	{
		try {
			$code = random_string("numeric", 6);

			$params["code"] = $code;
			$params["expires_in"] = Carbon::now()
				->addHours(24)
				->toDateTimeString();

			if ($this->get($params["account_id"])) {
				if (!$this->update($params["account_id"], $params)) {
					return false;
				}

				return $this->get($params["account_id"]);
			}

			if (!$this->__save(new OTP(), $params)) {
				return false;
			}

			return $this->get($code);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function update(string $id, array $params): bool
	{
		try {
			if (!($Code = $this->get($id))) {
				return false;
			}

			return $this->__update($Code, "account_id", $Code->account_id, $params);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function get(string $id): bool|null|OTP
	{
		try {
			return OTP::query()
				->where("account_id", $id)
				->orWhere("code", $id)
				->first();
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function invalidate(string $id): bool
	{
		try {
			if (!($Code = $this->get($id))) {
				return false;
			}

			return $this->__delete($Code, "account_id", $Code->account_id);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function isValid(string $id): bool
	{
		try {
			if (!($Code = $this->get($id))) {
				return false;
			}

			$now = Carbon::now();
			$timer = Carbon::createFromDate($Code->expires_in);

			return $timer > $now;
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}
}
