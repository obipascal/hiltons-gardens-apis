<?php namespace App\Http\Modules\Users;

use App\Http\Modules\Core\BaseModule;
use App\Http\Modules\Modules;
use App\Models\Account\User;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

use function App\Utilities\random_id;

class UsersModule
{
	use BaseModule;

	public function generateNewAccessToken(User $User): bool
	{
		try {
			$User->access_token = explode("|", $User->createToken("Personal Access Token")->plainTextToken)[1];

			return $User->save();
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function create(array $params): bool|User
	{
		try {
			$accountId = random_id();
			$params["account_id"] = $accountId;

			if (!$this->__save(new User(), $params)) {
				return false;
			}

			if (!$this->generateNewAccessToken($this->get($accountId))) {
				return false;
			}

			return $this->get($accountId);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function update(string $id, array $params): bool
	{
		try {
			if (!($User = $this->get($id))) {
				return false;
			}

			return $this->__update($User, "account_id", $User->account_id, $params);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function get(string $id): bool|null|User
	{
		try {
			return User::query()
				->where("account_id", $id)
				->orWhere("email", $id)
				->first();
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function all(int $perPage = 50): bool|Paginator
	{
		try {
			return User::query()
				->latest()
				->simplePaginate($perPage);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function delete(string $id): bool
	{
		try {
			if (!($User = $this->get($id))) {
				return false;
			}

			return $this->__delete($User, "account_id", $User->account_id);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function resetAccessToken(string $id): bool
	{
		try {
			if (!($User = $this->get($id))) {
				return false;
			}

			$User->tokens()->delete();

			return $this->generateNewAccessToken($User);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function exists(string $id): bool
	{
		try {
			return User::query()
				->where("account_id", $id)
				->orWhere("email", $id)
				->exists();
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}
}
