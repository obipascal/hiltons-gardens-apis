<?php namespace App\Http\Modules\Misc;

use App\Http\Modules\Core\BaseModule;
use App\Models\Misc\Favorites;

use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

use function App\Utilities\random_id;

class FavoriteModule
{
	use BaseModule;

	public function create(array $params): bool|null|Favorites
	{
		try {
			$params["favorite_id"] = random_id();

			if (!$this->__save(new Favorites(), $params)) {
				return false;
			}

			return $this->get($params["favorite_id"]);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function update(string $id, array $params): bool
	{
		try {
			if (!($favorite = $this->get($id))) {
				return false;
			}

			return $this->__update($favorite, "favorite_id", $favorite->favorite_id, $params);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function get(string $id): bool|null|Favorites
	{
		try {
			return Favorites::query()
				->where("favorite_id", $id)
				->first();
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function all(int $perPage = 50): bool|Paginator
	{
		try {
			return Favorites::query()
				->latest()
				->simplePaginate($perPage);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function getUserFavorites(string $id, int $perPage = 50): bool|Paginator
	{
		try {
			return Favorites::query()
				->where("account_id", $id)
				->latest()
				->simplePaginate($perPage);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}



	public function existsForUser(string $id, string $roomId): bool
	{
		try {
			return Favorites::query()
				->where(["room_id" => $roomId, "account_id" => $id])
				->exists();
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}



    public function deleteForUser(string $userId, string $id_or_roomId): bool
	{
		try {
			$item =  Favorites::query()
				->where(["room_id" => $id_or_roomId, "account_id" => $userId])
				->orWhere("favorite_id" , $id_or_roomId)
				->first();

                if(!$item) return false;

                return $this->delete($item->favorite_id);

		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

    public function delete(string $id): bool
    {
        try {

            return $this->__delete(new Favorites(), "favorite_id", $id);
        } catch (Exception $th) {
            Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
            return false;
        }
    }
}