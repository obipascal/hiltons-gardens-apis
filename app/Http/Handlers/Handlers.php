<?php namespace App\Http\Handlers;

use App\Http\Handlers\Auth\AuthHandler;
use App\Http\Handlers\Rooms\HotelRoomsHandler;
use App\Http\Handlers\Users\UsersHandler;

use Illuminate\Http\Request;

class Handlers
{
	public static function User(Request $request)
	{
		return new UsersHandler($request);
	}

	public static function Auth(Request $request)
	{
		return new AuthHandler($request);
	}

	public static function Rooms(Request $request)
	{
		return new HotelRoomsHandler($request);
	}
}