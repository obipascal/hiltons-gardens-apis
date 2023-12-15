<?php namespace App\Http\Modules\Rooms;

use App\Http\Modules\Core\BaseModule;
use App\Models\Rooms\HotelRooms;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

use function App\Utilities\random_id;

class HotelRoomsModule
{
	use BaseModule;

	public function create(array $params): bool|null|HotelRooms
	{
		try {
			$params["room_id"] = random_id();

			if (!$this->__save(new HotelRooms(), $params)) {
				return false;
			}

			return $this->get($params["room_id"]);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function update(string $id, array $params): bool
	{
		try {
			if (!($room = $this->get($id))) {
				return false;
			}

			return $this->__update($room, "room_id", $room->room_id, $params);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function get(string $id): bool|null|HotelRooms
	{
		try {
			return HotelRooms::query()
				->where("room_id", $id)
				->first();
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function all(int $perPage = 50): bool|Paginator
	{
		try {
			return HotelRooms::query()
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
			if (!($room = $this->get($id))) {
				return false;
			}

			return $this->__delete($room, "room_id", $room->room_id);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}
}