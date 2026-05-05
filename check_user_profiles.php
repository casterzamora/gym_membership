<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

use App\Models\User;

// Check for orphaned users without profiles
echo "=== Checking for orphaned user accounts ===\n\n";

echo "Members without member profiles:\n";
$members = User::where('role', 'member')->get();
foreach ($members as $user) {
    $member = $user->member;
    if (!$member) {
        echo "  - User ID {$user->id}: {$user->email} (NO MEMBER PROFILE)\n";
    }
}

echo "\nTrainers without trainer profiles:\n";
$trainers = User::where('role', 'trainer')->get();
foreach ($trainers as $user) {
    $trainer = $user->trainer;
    if (!$trainer) {
        echo "  - User ID {$user->id}: {$user->email} (NO TRAINER PROFILE)\n";
    }
}

echo "\n=== Testing user login flow for sample users ===\n";

// Try to load a sample member
echo "\nSample member (ID 2):\n";
try {
    $user = User::find(2);
    echo "  User found: {$user->email}, role: {$user->role}\n";
    $member = $user->member;
    if ($member) {
        echo "  Member profile exists: ID {$member->id}\n";
    } else {
        echo "  ERROR: No member profile!\n";
    }
} catch (\Exception $e) {
    echo "  ERROR: {$e->getMessage()}\n";
}

// Try to load a sample trainer  
echo "\nSample trainer (ID 51):\n";
try {
    $user = User::find(51);
    echo "  User found: {$user->email}, role: {$user->role}\n";
    $trainer = $user->trainer;
    if ($trainer) {
        echo "  Trainer profile exists: ID {$trainer->id}\n";
    } else {
        echo "  ERROR: No trainer profile!\n";
    }
} catch (\Exception $e) {
    echo "  ERROR: {$e->getMessage()}\n";
}
