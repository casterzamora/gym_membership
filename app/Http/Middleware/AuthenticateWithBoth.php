<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Models\Member;
use Illuminate\Http\Request;

class AuthenticateWithBoth
{
    public function handle(Request $request, Closure $next)
    {
        // Get the authorization header
        $header = $request->header('Authorization');
        
        if ($header && str_starts_with($header, 'Bearer ')) {
            $token = substr($header, 7);
            
            // Try to find the token in personal_access_tokens
            $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            
            if ($personalAccessToken) {
                // Try to find user first
                $user = User::find($personalAccessToken->tokenable_id);
                if ($user && $personalAccessToken->tokenable_type === User::class) {
                    auth()->setUser($user);
                    return $next($request);
                }
                
                // Try to find member
                $member = Member::find($personalAccessToken->tokenable_id);
                if ($member && $personalAccessToken->tokenable_type === Member::class) {
                    auth()->setUser($member);
                    return $next($request);
                }
            }
        }
        
        return $next($request);
    }
}
