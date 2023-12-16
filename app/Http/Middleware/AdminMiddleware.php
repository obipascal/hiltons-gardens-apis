<?php

namespace App\Http\Middleware;

use App\Enums\UsersTypes;
use App\Http\Config\RESTResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
	use RESTResponse;
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
	 */
	public function handle(Request $request, Closure $next): Response
	{
		if (!($user = $request->user())) {
			return $this->terminateRequest("Access unauthorized.", null, 403);
		}

		if ($user->role !== UsersTypes::ADMIN->value && $user->role !== UsersTypes::SUPPER->value) {
			return $this->terminateRequest("You do not have sufficient privileges to complete this action.", null, 403);
		}

		return $next($request);
	}
}