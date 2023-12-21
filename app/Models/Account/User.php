<?php

namespace App\Models\Account;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Payments\PaymentMethods;
use App\Models\Verification\OTP;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
	use HasApiTokens, HasFactory, Notifiable;

	/**
	 * Retrieve default relationships
	 *
	 *
	 */
	protected $with = ["billing"];
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = ["first_name", "last_name", "email", "password"];

	/**
	 * The attributes that should be hidden for serialization.
	 *
	 * @var array<int, string>
	 */
	protected $hidden = ["password", "access_token"];

	/**
	 * The attributes that should be cast.
	 *
	 * @var array<string, string>
	 */
	protected $casts = [
		"is_verified" => "boolean",
		"password" => "hashed",
	];

	public function otp()
	{
		return $this->hasOne(OTP::class, "account_id", "account_id");
	}

	public function accessToken(): Attribute
	{
		return Attribute::set(fn($value) => !empty($value) ? Crypt::encryptString($value) : $value);
	}

	public function billing()
	{
		return $this->hasOne(PaymentMethods::class, "account_id", "account_id");
	}
}
