<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\User;

class ApiAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');

        if (!$token || !str_starts_with($token, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized. No token provided.'], 401);
        }

        $token = substr($token, 7);

        $user = User::where('api_token', $token)->first();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized. Invalid token.'], 401);
        }

        $request->merge(['auth_user' => $user]);

        return $next($request);
    }
}
