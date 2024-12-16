<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TokenRefreshMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if ($user) {
            $roleId = $user->roles()->where('role_id', 3)->exists() ? 3 : null;

            if ($roleId === 3) {
                config(['jwt.ttl' => 10080]);
            } else {
                config(['jwt.ttl' => 120]);
            }
        }
        return $next($request);
    }
}
