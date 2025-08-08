<?php

namespace App\Http\Middleware;

use App\ResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    use ResponseTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->user_type !== $role) {

             //   return response()->json(['message' => 'Access denied. You do not have the required role.'], 403);
                return $this->getError(403,'Access denied. You do not have the required role.');
            }

            return $next($request);
        }

        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
