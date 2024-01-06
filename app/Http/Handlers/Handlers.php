<?php namespace App\Http\Handlers;

use App\Http\Handlers\Auth\AuthHandler;
use App\Http\Handlers\Bookings\BookingsHandler;
use App\Http\Handlers\Misc\FavoriteHandler;
use App\Http\Handlers\Misc\ReviewHandler;
use App\Http\Handlers\Rooms\HotelRoomsHandler;
use App\Http\Handlers\Users\UsersHandler;
use App\Http\Handlers\Webhooks\WebhooksHandler;
use Illuminate\Http\Request;
use App\Http\Handlers\Payments\PaymentHandler;

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

	public static function Webhook(Request $request)
	{
		return new WebhooksHandler($request);
	}

	public static function Payments(Request $request)
	{
		return new PaymentHandler($request);
	}

	public static function Booking(Request $request)
	{
		return new BookingsHandler($request);
	}

	public static function Favorites(Request $request)
	{
		return new FavoriteHandler($request);
	}

	public static function Reviews(Request $request)
	{
		return new ReviewHandler($request);
	}
}
