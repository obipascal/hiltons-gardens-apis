<?php

use App\Mail\AccountVerificationMail;
use App\Mail\BookingReservationDetailsMail;
use App\Models\Bookings\Bookings;
use Illuminate\Support\Facades\Route;
use App\Models\Account\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

    return view('email.paymentcallback', ['title' => 'Payment Callback', 'verificationCode' => '123412341234']);
});

Route::get('/app-callback', function(Request $request){

try {
    $reference  = $request->get('reference');
    $title = "Payment Callback";


    if(!empty($reference))
    {
        // verify payment status
        $paystackService = Paystack::Transaction()->verify($reference);
        if(!$paystackService->success)
        {
            $title = "Unable to verify transaction.";
        }


        // check if transaction successed
        if($paystackService->response->data->status === 'success' || $paystackService->response->data->status === 'reversed') {
            $title = 'Transaction was successful';
        }

    }
    return view('email.paymentcallback', ['title' => $title,  'reference' => $reference ?? "123412341234"]);
} catch (Exception $th) {
    $title = "Something went wrong while processing transaction.";
    return view('email.paymentcallback', ['title' => $title,  'reference' => $reference ?? "123412341234"]);
}

})->name('app-callback');