<?php namespace App\Http\Modules\Bookings;

use App\Http\Modules\Core\BaseModule;
use App\Models\Bookings\Bookings;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

use function App\Utilities\random_id;

class BookingsModule
{
	use BaseModule;

	public function create(array $params): bool|null|Bookings
	{
		try {
			$params["booking_id"] = random_id();

			if (!$this->__save(new Bookings(), $params)) {
				return false;
			}

			return $this->get($params["booking_id"]);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function update(string $id, array $params): bool
	{
		try {
			if (!($booking = $this->get($id))) {
				return false;
			}

			return $this->__update($booking, "booking_id", $booking->booking_id, $params);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function get(string $id): bool|null|Bookings
	{
		try {
			return Bookings::query()
				->where("booking_id", $id)
				->orWhere("trans_id", $id)
				->first();
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function all(int $perPage = 50): bool|Paginator
	{
		try {
			return Bookings::query()
				->latest()
				->simplePaginate($perPage);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}

	public function getUserBookings(string $id, int $perPage = 50): bool|Paginator
	{
		try {
			return Bookings::query()
				->where("account_id", $id)
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
			if (!($booking = $this->get($id))) {
				return false;
			}

			return $this->__delete($booking, "booking_id", $booking->booking_id);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
			return false;
		}
	}
}
