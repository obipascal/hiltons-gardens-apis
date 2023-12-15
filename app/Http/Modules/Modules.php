<?php namespace App\Http\Modules;

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
}