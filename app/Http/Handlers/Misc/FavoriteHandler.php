<?php namespace App\Http\Handlers\Misc;

use App\Http\Handlers\Core\BaseHandler;
use App\Http\Modules\Modules;
use App\Models\Account\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FavoriteHandler
{
	use BaseHandler;

	public function addToFavorite()
	{
		try {
			$params = $this->request->all(["room_id"]);

			// Get the current user

			/** @var User */
			$user = $this->request->user();

			// Check if the provided room already exist for this users
			if (Modules::Favorites()->existsForUser($user->account_id, $params["room_id"])) {
				return $this->raise("Sorry this room already exists.");
			}

			$responseData = DB::transaction(function () use ($params, $user) {
				// Prepare database data
				$favoriteData = [
					"account_id" => $user->account_id,
					"room_id" => $params["room_id"],
				];

				// Create favorite

				if (!($favorite = Modules::Favorites()->create($favoriteData))) {
					throw new Exception("Unable to add room to favorite.");
				}

				return $favorite;
			}, attempts: 1);

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Room added to favorite";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 201;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function fetchFavorites()
	{
		try {
			$params = $this->request->all([""]);

			// Get current user

			/** @var User */
			$user = $this->request->user();
			$perPage = $this->request->get("perPage") ?? 50;

			$responseData = Modules::Favorites()->getUserFavorites($user->account_id, $perPage);

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "User favorites retrieved";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 200;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function removeFavorite(string $id)
	{
		try {
			$params = $this->request->all([""]);

			if (!Modules::Favorites()->delete($id)) {
				return $this->raise("Unable to retrieve from favorite.");
			}

			$responseData = null;

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Room removed from favorites.";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 204;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}
}
