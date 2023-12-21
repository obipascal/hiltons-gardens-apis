<?php

namespace App\Models\Payments;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PaymentMethods extends Model
{
	use HasFactory;

	/**
	 * The attributes that should be hidden for serialization.
	 *
	 * @var array<int, string>
	 */
	protected $hidden = ["auth_token"];

	public function authToken(): Attribute
	{
		return Attribute::make(set: fn($value) => !empty($value) ? Crypt::encryptString($value) : $value, get: fn($value) => !empty($value) ? Crypt::decryptString($value) : $value);
	}
}
