<?php namespace App\Http\Handlers\Payments;

use App\Http\Handlers\Core\BaseHandler;
use App\Http\Handlers\Payments\PaymentMethodHandler;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentHandler
{
	use BaseHandler, PaymentMethodHandler;
}