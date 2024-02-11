<?php

namespace App\Models\Bookings;

use App\Models\Payments\Transactions;
use App\Models\Rooms\HotelRooms;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookings extends Model
{
	use HasFactory;

	protected $with = ["transaction", "room"];

	public function transaction()
	{
		return $this->belongsTo(Transactions::class, "trans_id", "trans_id");
	}

	public function room()
	{
		return $this->belongsTo(HotelRooms::class, "room_id", "room_id");
	}


}