<?php namespace App\Http\Handlers\Users;

use App\Http\Modules\Modules;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

trait UserHelpers
{
	protected function getUser(string $id, bool $withToken = true)
	{
		$User = Modules::User()->get($id);
		if ($withToken) {
			$User->api_token = $User->access_token;
		}

		return $User;
	}
}
