<?php

namespace App\Models\Rooms;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelRooms extends Model
{
	use HasFactory;

	public function name(): Attribute
	{
		return Attribute::set(fn($value) => !empty($value) ? ucwords($value) : $value);
	}

	public function images(): Attribute
	{
		return Attribute::make(get: fn($value) => !empty($value) ? json_decode($value) : $value);
	}
}