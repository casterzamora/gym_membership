<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;
use App\Models\Member;

class DualSanctumAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Try to authenticate with both User and Member providers
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['success' => false, 'message' => 'No token provided'], 401);
        }

        if (!$token) {
            return response()->json(['success' => false, 'message' => 'No token provided'], 401);
        }

        // Find the personal access token
        $personalAccessToken = PersonalAccessToken::findToken($token);

        if (!$personalAccessToken) {
            return response()->json(['success' => false, 'message' => 'Invalid token'], 401);
        }

        // Try to get the user based on tokenable_type
        $user = null;
        if ($personalAccessToken->tokenable_type === User::class) {
            $user = User::find($personalAccessToken->tokenable_id);
        } elseif ($personalAccessToken->tokenable_type === Member::class) {
            $user = Member::find($personalAccessToken->tokenable_id);
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 401);
        }

        // Set the user on the request
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
