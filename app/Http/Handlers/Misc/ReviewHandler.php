<?php namespace App\Http\Handlers\Misc;

use App\Http\Handlers\Core\BaseHandler;
use App\Http\Modules\Modules;
use App\Models\Account\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReviewHandler
{
	use BaseHandler;

	public function createReview()
	{
		try {
			$params = $this->request->all(["room_id", "rating", "message"]);

			/** @var User */
			$user = $this->request->user();

			// Check if user has already posted a review for this room
			if (Modules::Reviews()->existForUser($user->account_id, $params["room_id"])) {
				// Get the review
				$review = Modules::Reviews()->getForUser($user->account_id, $params["room_id"]);

				$responseData = DB::transaction(function () use ($params, $user, $review) {
					// Prepare database data
					$params["account_id"] = $user->account_id;

					// Update review
					if (!Modules::Reviews()->update($review->review_id, $params)) {
						throw new Exception("Unable to create review");
					}
					// Fetch fresh data
					return Modules::Reviews()->get($review->review_id);
				}, attempts: 1);
			} else {
				// Create new
				$responseData = DB::transaction(function () use ($params, $user) {
					// Prepare database data
					$params["account_id"] = $user->account_id;

					if (!($review = Modules::Reviews()->create($params))) {
						throw new Exception("Unable to create review");
					}

					return $review;
				}, attempts: 1);
			}

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Review created successfully!";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 201;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function fetchReviews(string $roomId)
	{
		try {
			$params = $this->request->all([""]);

			$perPage = $this->request->get("perPage") ?? 50;

			// Fetch room reviews
			$responseData = Modules::Reviews()->getForRoom($roomId, $perPage);

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Success";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 200;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function deleteReview(string $id)
	{
		try {
			$params = $this->request->all([""]);

			if (!Modules::Reviews()->delete($id)) {
				return $this->raise("Unable to delete review");
			}

			$responseData = null;
			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Success";
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
