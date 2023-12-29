<?php

use App\Mail\AccountVerificationMail;
use App\Mail\BookingReservationDetailsMail;
use App\Models\Bookings\Bookings;
use Illuminate\Support\Facades\Route;
use App\Models\Account\User;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get("/", function () {
	dd(Carbon::createFromDate("2023-12-30T11:01:00.000Z")->toDateTimeString());

	$booking = Bookings::where("booking_id", "3770904316539")->first();
	$user = User::where("account_id", $booking->account_id)->first();

	return (new BookingReservationDetailsMail($user, $booking))->render();
});
