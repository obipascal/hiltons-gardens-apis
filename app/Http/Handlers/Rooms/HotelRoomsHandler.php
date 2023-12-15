<?php namespace App\Http\Handlers\Rooms;

use App\Http\Handlers\Core\BaseHandler;
use App\Http\Modules\Modules;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class HotelRoomsHandler
{
	use BaseHandler;

	public function processImageUpload()
	{
		if (!($file = $this->request->file("image"))) {
			return $this->raise("Please upload a primary room image.");
		}

		if (!$file->isValid()) {
			return $this->raise("Invalid file uploaded");
		}

		$folder = "hotel_rooms/";

		return $file->storePublicly($folder);
	}

	public function addRoom()
	{
		try {
			$params = $this->request->all(["name", "desc", "price"]);

			// Step 1: Upload
			if (!is_string($image = $this->processImageUpload())) {
				return $this->processImageUpload();
			}
			$params["image"] = $image;

			$responseData = DB::transaction(function () use ($params) {
				// Step 2: Process and store room details
				if (!($room = Modules::Room()->create($params))) {
					throw new Exception("Unable to to create room.");
				}

				// Step 3: return room
				return $room;
			}, attempts: 1);

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Room created";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 201;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function updateRoom(string $id)
	{
		try {
			$params = $this->request->all(["name", "desc", "price"]);

			// Start: Fetch the room
			if (!($room = Modules::Room()->get($id))) {
				return $this->raise("Oops, room was not found!", null, 404);
			}

			// Step 1: Check if an image was uploaded
			if (!empty($this->request->file("image"))) {
				// Step 2: If image is uploaded, prcess it and assign the new path
				if (!is_string($image = $this->processImageUpload())) {
					return $this->processImageUpload();
				}

				$params["image"] = $image;
			}

			$responseData = DB::transaction(function () use ($params, $id) {
				// Step 3: Update room
				if (!Modules::Room()->update($id, $params)) {
					throw new Exception("Unable to update room details.");
				}

				// Step 4: return fresh room data
				return Modules::Room()->get($id);
			}, attempts: 1);

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Room updated";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 200;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function fetchRoom(string $id)
	{
		try {
			$params = $this->request->all([""]);

			if (!($responseData = Modules::Room()->get($id))) {
				return $this->raise("Oops, no room found with this ID.", null, 404);
			}

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Room retrieved";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 200;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function fetchRoomS()
	{
		try {
			$params = $this->request->all([""]);
			$perPage = $this->request->get("perPage") ?? 100;

			if (!($responseData = Modules::Room()->all($perPage))) {
				return $this->raise("Oops, no room found with this ID.", null, 404);
			}

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Rooms retrieved";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 200;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function deleteRoom(string $id)
	{
		try {
			$params = $this->request->all([""]);

			if (!($responseData = Modules::Room()->delete($id))) {
				return $this->raise("Oops, no room found with this ID.", null, 404);
			}

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Room deleted";
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