<?php namespace App\Http\Modules;

use App\Http\Modules\Bookings\BookingsModule;
use App\Http\Modules\Misc\FavoriteModule;
use App\Http\Modules\Misc\ReviewsModule;
use App\Http\Modules\Payments\TransactionsModule;
use App\Http\Modules\Rooms\HotelRoomsModule;
use App\Http\Modules\Security\OTPModule;
use App\Http\Modules\Users\UsersModule;

class Modules
{
	public static function User()
	{
		return new UsersModule();
	}

	public static function OTP()
	{
		return new OTPModule();
	}

	public static function Room()
	{
		return new HotelRoomsModule();
	}

	public static function Bookings()
	{
		return new BookingsModule();
	}

	public static function Payments()
	{
		return new TransactionsModule();
	}

	public static function Reviews()
	{
		return new ReviewsModule();
	}

	public static function Favorites()
	{
		return new FavoriteModule();
	}
}
