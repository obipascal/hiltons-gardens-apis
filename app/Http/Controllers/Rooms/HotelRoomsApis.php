<?php

namespace App\Http\Controllers\Rooms;

use App\Http\Config\RESTResponse;
use App\Http\Controllers\Controller;
use App\Http\Handlers\Handlers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HotelRoomsApis extends Controller
{
	use RESTResponse;
	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		try {
			/* Run validation  */
			$validator = Validator::make(request()->all(), [
				"perPage" => ["bail", "numeric", "nullable"],
			]);

			/* Check if any validation fails */
			if ($validator->fails()) {
				/* If fails return the validation error message  */
				return $this->terminateRequest("Validation Error", $this->getValidationMessages($validator));
			}
			/* Call the controller handlers to handle request logic */
			$handler = Handlers::Rooms(request())->fetchRooms();

			/* Determine handler operation status  */
			if (!$handler->STATE) {
				/* If operation didn't succeed return the error that was generated by the operation */
				return $this->terminateRequest($handler->ERROR, $handler->RESPONSE, $handler->CODE);
			}

			/* Finally all went well return the response to caller/client */
			return $this->sendResponse($handler->RESPONSE, $handler->MESSAGE, $handler->STATE, $handler->CODE);
		} catch (\Exception $th) {
			Log::error($th->getMessage(), ["file" => $th->getFile(), "line" => $th->getLine()]);

			return $this->terminateRequest("ERROR", $this->RESPONSE ?? $th->getMessage(), 500);
		}
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		try {
			/* Run validation  */
			$validator = Validator::make($request->all(), [
				"name" => ["bail", "string", "required", "unique:hotel_rooms,name"],
				"desc" => ["bail", "string", "required"],
				"price" => ["bail", "string", "required"],
				"image" => ["bail", "image", "required", "mimes:png,jpg,jpeg"],
			]);

			/* Check if any validation fails */
			if ($validator->fails()) {
				/* If fails return the validation error message  */
				return $this->terminateRequest("Validation Error", $this->getValidationMessages($validator));
			}
			/* Call the controller handlers to handle request logic */
			$handler = Handlers::Rooms($request)->createRoom();

			/* Determine handler operation status  */
			if (!$handler->STATE) {
				/* If operation didn't succeed return the error that was generated by the operation */
				return $this->terminateRequest($handler->ERROR, $handler->RESPONSE, $handler->CODE);
			}

			/* Finally all went well return the response to caller/client */
			return $this->sendResponse($handler->RESPONSE, $handler->MESSAGE, $handler->STATE, $handler->CODE);
		} catch (\Exception $th) {
			Log::error($th->getMessage(), ["file" => $th->getFile(), "line" => $th->getLine()]);

			return $this->terminateRequest("ERROR", $this->RESPONSE ?? $th->getMessage(), 500);
		}
	}

	/**
	 * Display the specified resource.
	 */
	public function show(string $id)
	{
		try {
			/* Run validation  */
			$validator = Validator::make(
				["room_id" => $id],
				[
					"room_id" => ["bail", "numeric", "required", "exists:hotel_rooms,room_id"],
				]
			);

			/* Check if any validation fails */
			if ($validator->fails()) {
				/* If fails return the validation error message  */
				return $this->terminateRequest("Validation Error", $this->getValidationMessages($validator));
			}
			/* Call the controller handlers to handle request logic */
			$handler = Handlers::Rooms(request())->fetchRoom($id);

			/* Determine handler operation status  */
			if (!$handler->STATE) {
				/* If operation didn't succeed return the error that was generated by the operation */
				return $this->terminateRequest($handler->ERROR, $handler->RESPONSE, $handler->CODE);
			}

			/* Finally all went well return the response to caller/client */
			return $this->sendResponse($handler->RESPONSE, $handler->MESSAGE, $handler->STATE, $handler->CODE);
		} catch (\Exception $th) {
			Log::error($th->getMessage(), ["file" => $th->getFile(), "line" => $th->getLine()]);

			return $this->terminateRequest("ERROR", $this->RESPONSE ?? $th->getMessage(), 500);
		}
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, string $id)
	{
		try {
			/* Run validation  */
			$validator = Validator::make(
				["room_id" => $id, ...$request->all()],
				[
					"room_id" => ["bail", "numeric", "required", "exists:hotel_rooms,room_id"],
					"name" => ["bail", "string", "required", "unique:hotel_rooms,name"],
					"desc" => ["bail", "string", "required"],
					"price" => ["bail", "string", "required"],
				]
			);

			/* Check if any validation fails */
			if ($validator->fails()) {
				/* If fails return the validation error message  */
				return $this->terminateRequest("Validation Error", $this->getValidationMessages($validator));
			}
			/* Call the controller handlers to handle request logic */
			$handler = Handlers::Rooms($request)->updateRoom($id);

			/* Determine handler operation status  */
			if (!$handler->STATE) {
				/* If operation didn't succeed return the error that was generated by the operation */
				return $this->terminateRequest($handler->ERROR, $handler->RESPONSE, $handler->CODE);
			}

			/* Finally all went well return the response to caller/client */
			return $this->sendResponse($handler->RESPONSE, $handler->MESSAGE, $handler->STATE, $handler->CODE);
		} catch (\Exception $th) {
			Log::error($th->getMessage(), ["file" => $th->getFile(), "line" => $th->getLine()]);

			return $this->terminateRequest("ERROR", $this->RESPONSE ?? $th->getMessage(), 500);
		}
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(string $id)
	{
		try {
			/* Run validation  */
			$validator = Validator::make(
				["room_id" => $id],
				[
					"room_id" => ["bail", "numeric", "required", "exists:hotel_rooms,room_id"],
				]
			);

			/* Check if any validation fails */
			if ($validator->fails()) {
				/* If fails return the validation error message  */
				return $this->terminateRequest("Validation Error", $this->getValidationMessages($validator));
			}
			/* Call the controller handlers to handle request logic */
			$handler = Handlers::Rooms(request())->deleteRoom($id);

			/* Determine handler operation status  */
			if (!$handler->STATE) {
				/* If operation didn't succeed return the error that was generated by the operation */
				return $this->terminateRequest($handler->ERROR, $handler->RESPONSE, $handler->CODE);
			}

			/* Finally all went well return the response to caller/client */
			return $this->sendResponse($handler->RESPONSE, $handler->MESSAGE, $handler->STATE, $handler->CODE);
		} catch (\Exception $th) {
			Log::error($th->getMessage(), ["file" => $th->getFile(), "line" => $th->getLine()]);

			return $this->terminateRequest("ERROR", $this->RESPONSE ?? $th->getMessage(), 500);
		}
	}

	// ----------------->[Customer Endpoints]-----------------

	/**
	 * Update the specified resource in storage.
	 */
	public function updateImage(Request $request, string $id)
	{
		try {
			/* Run validation  */
			$validator = Validator::make(
				["room_id" => $id, ...$request->all()],
				[
					"room_id" => ["bail", "numeric", "required", "exists:hotel_rooms,room_id"],
					"image" => ["bail", "image", "required", "mimes:png,jpg,jpeg"],
				]
			);

			/* Check if any validation fails */
			if ($validator->fails()) {
				/* If fails return the validation error message  */
				return $this->terminateRequest("Validation Error", $this->getValidationMessages($validator));
			}
			/* Call the controller handlers to handle request logic */
			$handler = Handlers::Rooms($request)->updateRoomImage($id);

			/* Determine handler operation status  */
			if (!$handler->STATE) {
				/* If operation didn't succeed return the error that was generated by the operation */
				return $this->terminateRequest($handler->ERROR, $handler->RESPONSE, $handler->CODE);
			}

			/* Finally all went well return the response to caller/client */
			return $this->sendResponse($handler->RESPONSE, $handler->MESSAGE, $handler->STATE, $handler->CODE);
		} catch (\Exception $th) {
			Log::error($th->getMessage(), ["file" => $th->getFile(), "line" => $th->getLine()]);

			return $this->terminateRequest("ERROR", $this->RESPONSE ?? $th->getMessage(), 500);
		}
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function updateImages(Request $request, string $id)
	{
		try {
			/* Run validation  */
			$validator = Validator::make(
				["room_id" => $id, ...$request->all()],
				[
					"room_id" => ["bail", "numeric", "required", "exists:hotel_rooms,room_id"],
					"images.*" => ["bail", "image", "required", "mimes:png,jpg,jpeg"],
				]
			);

			/* Check if any validation fails */
			if ($validator->fails()) {
				/* If fails return the validation error message  */
				return $this->terminateRequest("Validation Error", $this->getValidationMessages($validator));
			}
			/* Call the controller handlers to handle request logic */
			$handler = Handlers::Rooms($request)->updateRoomImages($id);

			/* Determine handler operation status  */
			if (!$handler->STATE) {
				/* If operation didn't succeed return the error that was generated by the operation */
				return $this->terminateRequest($handler->ERROR, $handler->RESPONSE, $handler->CODE);
			}

			/* Finally all went well return the response to caller/client */
			return $this->sendResponse($handler->RESPONSE, $handler->MESSAGE, $handler->STATE, $handler->CODE);
		} catch (\Exception $th) {
			Log::error($th->getMessage(), ["file" => $th->getFile(), "line" => $th->getLine()]);

			return $this->terminateRequest("ERROR", $this->RESPONSE ?? $th->getMessage(), 500);
		}
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function toggleStatus(Request $request, string $id)
	{
		try {
			/* Run validation  */
			$validator = Validator::make(
				["room_id" => $id, ...$request->all()],
				[
					"room_id" => ["bail", "numeric", "required", "exists:hotel_rooms,room_id"],
					"status" => ["bail", "string", "required", Rule::in(["active", "suspended"])],
				]
			);

			/* Check if any validation fails */
			if ($validator->fails()) {
				/* If fails return the validation error message  */
				return $this->terminateRequest("Validation Error", $this->getValidationMessages($validator));
			}
			/* Call the controller handlers to handle request logic */
			$handler = Handlers::Rooms($request)->toggleRoomStatus($id);

			/* Determine handler operation status  */
			if (!$handler->STATE) {
				/* If operation didn't succeed return the error that was generated by the operation */
				return $this->terminateRequest($handler->ERROR, $handler->RESPONSE, $handler->CODE);
			}

			/* Finally all went well return the response to caller/client */
			return $this->sendResponse($handler->RESPONSE, $handler->MESSAGE, $handler->STATE, $handler->CODE);
		} catch (\Exception $th) {
			Log::error($th->getMessage(), ["file" => $th->getFile(), "line" => $th->getLine()]);

			return $this->terminateRequest("ERROR", $this->RESPONSE ?? $th->getMessage(), 500);
		}
	}
}