<?php
require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;
use App\Models\Member;

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get last 5 tokens
$tokens = PersonalAccessToken::latest()->limit(5)->get();

foreach ($tokens as $token) {
    echo "Token ID: {$token->id} | Partial: " . substr($token->token, 0, 10) . "...\n";
    echo "  Tokenable Type: {$token->tokenable_type}\n";
    echo "  Tokenable ID: {$token->tokenable_id}\n";
    
    // Try to find the user
    if ($token->tokenable_type === User::class) {
        $user = User::find($token->tokenable_id);
        echo "  Found User: {$user?->email}\n";
    } elseif ($token->tokenable_type === Member::class) {
        $member = Member::find($token->tokenable_id);
        echo "  Found Member: {$member?->email}\n";
    }
    echo "\n";
}
