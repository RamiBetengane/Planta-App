<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
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
        // تحقق إذا كان المستخدم مسجلاً دخول
        if (Auth::check()) {
            // تحقق من الدور الخاص بالمستخدم
            $user = Auth::user();

            // إذا كان الدور غير مطابق، يمكن إعادة رد خطأ
            if ($user->user_type !== $role) {
                return response()->json(['message' => 'Access denied. You do not have the required role.'], 403);
            }

            // إذا كان الدور مطابق، استمر في الطلب
            return $next($request);
        }

        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
