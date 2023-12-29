<?php

namespace App\Models\Payments;

use App\Models\Bookings\Bookings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
	use HasFactory;

	public function booking()
	{
		return $this->belongsTo(Bookings::class, "booking_id", "booking_id");
	}
}
