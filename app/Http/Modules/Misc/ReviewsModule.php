<?php

namespace App\Http\Modules\Misc;

use App\Http\Modules\Core\BaseModule;
use App\Models\Misc\Reviews;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

use function App\Utilities\random_id;

class ReviewsModule
{
    use BaseModule;

    public function create(array $params): bool|null|Reviews
    {
        try {
            $params["review_id"] = random_id();

            if (!$this->__save(new Reviews(), $params)) {
                return false;
            }

            return $this->get($params["review_id"]);
        } catch (Exception $th) {
            Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
            return false;
        }
    }

    public function update(string $id, array $params): bool
    {
        try {
            if (!($review = $this->get($id))) {
                return false;
            }

            return $this->__update($review, "review_id", $review->review_id, $params);
        } catch (Exception $th) {
            Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
            return false;
        }
    }

    public function get(string $id): bool|null|Reviews
    {
        try {
            return Reviews::query()
                ->where("review_id", $id)
                ->first();
        } catch (Exception $th) {
            Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
            return false;
        }
    }

    public function all(int $perPage = 50): bool|Paginator
    {
        try {
            return Reviews::query()
                ->latest()
                ->simplePaginate($perPage);
        } catch (Exception $th) {
            Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
            return false;
        }
    }


    public function getForRoom(string $roomId, int $perPage = 50): bool|Collection
    {
        try {
            return Reviews::query()
                ->where("room_id", $roomId)
                ->latest()
                ->get();
        } catch (Exception $th) {
            Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
            return false;
        }
    }

    public function delete(string $id): bool
    {
        try {
            if (!($review = $this->get($id))) {
                return false;
            }

            return $this->__delete($review, "review_id", $review->review_id);
        } catch (Exception $th) {
            Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
            return false;
        }
    }

    public function existForUser(string $accountId, string $roomId): bool
    {
        try {
            return Reviews::query()
                ->where(["account_id" => $accountId, "room_id" => $roomId])
                ->exists();
        } catch (Exception $th) {
            Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
            return false;
        }
    }

    public function getForUser(string $accountId, string $roomId): bool|Reviews
    {
        try {
            return Reviews::query()
                ->where(["account_id" => $accountId, "room_id" => $roomId])
                ->first();
        } catch (Exception $th) {
            Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);
            return false;
        }
    }
}
