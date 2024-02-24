<?php namespace App\Http\Handlers\Rooms;

use App\Http\Handlers\Core\BaseHandler;
use App\Http\Modules\Modules;
use App\Models\Rooms\HotelRooms;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HotelRoomsHandler
{
	use BaseHandler;

	public function deleteImages(HotelRooms $hotelRooms): bool
	{
		if ($room = $hotelRooms) {
			// obtain the file path
			$filePath = Str::replace("storage", "public", $room->image);

			// check if file exist
			if (Storage::exists($filePath)) {
				// delete the file
				if (Storage::delete($filePath)) {
					// Delete images if present
					if (!empty($room->images) && is_array($room->images)) {
						foreach ($room->images as $image) {
							$imagePath = Str::replace("storage", "public", $image);
							if (Storage::exists($imagePath)) {
								// delete the file
								Storage::delete($imagePath);
							}
						}
					}
				}
			}
		}

		return true;
	}

	public function processImageUpload(?string $id = null)
	{
		// Start: Confirm if file was uploaded
		if (!($file = $this->request->file("image"))) {
			return $this->raise("Please upload a primary room image.");
		}

		// Step 1: Check if the provided id is not empty, and if it isn't get the room and delete previous image
		if (!empty($id)) {
			// Get the room
			if ($room = Modules::Room()->get($id)) {
				// obtain the file path
				$filePath = Str::replace("storage", "public", $room->image);

				// check if file exist
				if (Storage::exists($filePath)) {
					// delete the file
					Storage::delete($filePath);
				}
			}
		}

		// Step 2: Upload the new file and return the file path

		// Check if file upload is valid
		if (!$file->isValid()) {
			return $this->raise("Invalid file uploaded");
		}

		// Map the file path
		$folder = "public/hotel_rooms";

		$path = $file->storePublicly($folder);

		// swap the publicly accessable path and return the path.
		return Str::replace("public", "storage", $path);
	}

	public function processImagesUpload(?string $id = null)
	{
		// Start: Confirm if file was uploaded
		if (!$this->request->file("images")) {
			return $this->raise("Please upload a primary room image(s).");
		}

		// Step 1: Check if the provided id is not empty, and if it isn't get the room and delete previous image
		if (!empty($id)) {
			// Get the room
			if ($room = Modules::Room()->get($id)) {
				if (!empty($room->images) && is_array($room->images)) {
					foreach ($room->images as $image) {
						// obtain the file path
						$filePath = Str::replace("storage", "public", $image);

						// check if file exist
						if (Storage::exists($filePath)) {
							// delete the file
							Storage::delete($filePath);
						}
					}
				}
			}
		}

		// Step 2: Upload the new file and return the file path

		$uploadedFiles = [];
		foreach ($this->request->file("images") as $key => $file) {
			// Check if file upload is valid
			if (!$file->isValid()) {
				return $this->raise("Invalid file uploaded");
			}

			// Map the file path
			$folder = "public/hotel_rooms";

			$path = $file->storePublicly($folder);

			// swap the publicly accessable path and return the path.
			array_push($uploadedFiles, Str::replace("public", "storage", $path));
		}

		return $uploadedFiles;
	}

	public function createRoom()
	{
		try {
			$params = $this->request->all(["name", "desc", "about", "price"]);

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
			$params = $this->request->all(["name", "desc", "about", "price"]);

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

            $userId = $this->request->get('user_id') ?? null;

			if (!($responseData = Modules::Room()->get($id))) {
				return $this->raise("Oops, no room found with this ID.", null, 404);
			}
            // Check if room is in user's favorite
           if(!empty($userId)) {
               $responseData->isFavorite = Modules::Favorites()->existsForUser($userId, $responseData->room_id);
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

	public function fetchRooms()
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

			//Step 1: Get the room
			if (!($room = Modules::Room()->get($id))) {
				return $this->raise("Oops, room not found", null, 404);
			}

			// Step 2: Delete room
			if (!($responseData = Modules::Room()->delete($id))) {
				return $this->raise("Oops, no room found with this ID.", null, 404);
			}

			// Step 3: Delete room images
			$this->deleteImages($room);

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

	// -----------------[Others]---------------------

	public function updateRoomImage(string $id)
	{
		try {
			// Step 1: Upload image
			if (!is_string($image = $this->processImageUpload($id))) {
				return $this->processImageUpload($id);
			}
			$params["image"] = $image;

			$responseData = DB::transaction(function () use ($params, $id) {
				if (!Modules::Room()->update($id, $params)) {
					throw new Exception("Unable to update room image at the moment.");
				}

				return Modules::Room()->get($id);
			}, attempts: 1);

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Room image updated.";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 200;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function updateRoomImages(string $id)
	{
		try {
			// Step 1: Upload image
			if (!is_array($images = $this->processImagesUpload($id))) {
				return $this->processImagesUpload($id);
			}

			$params["images"] = $images;

			$responseData = DB::transaction(function () use ($params, $id) {
				if (!Modules::Room()->update($id, $params)) {
					throw new Exception("Unable to update room image at the moment.");
				}

				return Modules::Room()->get($id);
			}, attempts: 1);

			//-----------------------------------------------------

			/** Request response data */
			$responseMessage = "Room images uploaded.";
			$response["type"] = "";
			$response["body"] = $responseData;
			$responseCode = 200;

			return $this->response($response, $responseMessage, $responseCode);
		} catch (Exception $th) {
			Log::error($th->getMessage(), ["Line" => $th->getLine(), "file" => $th->getFile()]);

			return $this->raise($th->getMessage(), null, 400);
		}
	}

	public function toggleRoomStatus(string $id)
	{
		try {
			$params = $this->request->all(["status"]);

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
}
