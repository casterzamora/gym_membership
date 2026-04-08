<?php

use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;
use App\Models\Member;

// Get recent tokens
$tokens = PersonalAccessToken::latest()->limit(5)->get();

echo "=== Recent Tokens ===\n\n";

foreach ($tokens as $token) {
    echo "Tokenable Type: " . $token->tokenable_type . "\n";
    echo "Tokenable ID: " . $token->tokenable_id . "\n";
    echo "Token (first 15 chars): " . substr($token->token, 0, 15) . "...\n";
    echo "\n";
}

// Check what User and Member class names are
echo "\n=== User and Member FQN ===\n";
echo "User::class = " . User::class . "\n";
echo "Member::class = " . Member::class . "\n";
