<?php

namespace App\Auth;

use Laravel\Sanctum\Guards\SanctumGuard;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;
use App\Models\Member;

class DualSanctumGuard extends SanctumGuard
{
    /**
     * Get the currently authenticated user.
     */
    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $token = $this->getTokenFromRequest();

        if (!$token) {
            return null;
        }

        // Find the personal access token
        $personalAccessToken = PersonalAccessToken::findToken($token);

        if (!$personalAccessToken) {
            return null;
        }

        // Support both User and Member models
        if ($personalAccessToken->tokenable_type === User::class) {
            return $this->user = User::find($personalAccessToken->tokenable_id);
        } elseif ($personalAccessToken->tokenable_type === Member::class) {
            return $this->user = Member::find($personalAccessToken->tokenable_id);
        }

        return null;
    }

    /**
     * Extract the token from the request.
     */
    private function getTokenFromRequest()
    {
        $header = $this->request->header('Authorization', '');

        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return null;
    }
}
