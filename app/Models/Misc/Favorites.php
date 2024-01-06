<?php

namespace App\Models\Misc;

use App\Models\Rooms\HotelRooms;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorites extends Model
{
	use HasFactory;
	protected $with = ["room"];

	public function room()
	{
		return $this->belongsTo(HotelRooms::class, "room_id", "room_id");
	}
}
