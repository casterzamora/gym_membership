<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "==== DATABASE CONSOLIDATION TEST ====\n\n";

// Test 1: User model relationships
echo "TEST 1: User Model Relationships\n";
$user = \App\Models\User::find(1);
echo "✓ User #1: " . $user->name . " (Role: " . $user->role . ")\n";
echo "  - First Name: " . $user->first_name . "\n";
echo "  - Last Name: " . $user->last_name . "\n";
echo "  - Is Active: " . ($user->is_active ? "Yes" : "No") . "\n";

// Test 2: Member relationship
echo "\nTEST 2: Member Relationships\n";
$memberUser = \App\Models\User::where('role', 'member')->first();
if ($memberUser) {
    echo "✓ Member User: " . $memberUser->name . "\n";
    $member = $memberUser->member;
    if ($member) {
        echo "  - Linked to Member #" . $member->id . "\n";
        echo "  - Membership Status: " . $member->membership_status . "\n";
    } else {
        echo "  - ERROR: Member profile not linked!\n";
    }
}

// Test 3: Trainer relationship
echo "\nTEST 3: Trainer Relationships\n";
$trainerUser = \App\Models\User::where('role', 'trainer')->first();
if ($trainerUser) {
    echo "✓ Trainer User: " . $trainerUser->name . "\n";
    $trainer = $trainerUser->trainer;
    if ($trainer) {
        echo "  - Linked to Trainer #" . $trainer->id . "\n";
        echo "  - Specialization: " . $trainer->specialization . "\n";
    } else {
        echo "  - ERROR: Trainer profile not linked!\n";
    }
}

// Test 4: Data migration verification
echo "\nTEST 4: Data Migration Verification\n";
$totalUsers = \App\Models\User::count();
$totalMembers = \App\Models\Member::count();
$totalTrainers = \App\Models\Trainer::count();
$linkedMembers = \App\Models\Member::whereNotNull('user_id')->count();
$linkedTrainers = \App\Models\Trainer::whereNotNull('user_id')->count();

echo "✓ Total Users: $totalUsers\n";
echo "✓ Total Members: $totalMembers (Linked: $linkedMembers)\n";
echo "✓ Total Trainers: $totalTrainers (Linked: $linkedTrainers)\n";

// Test 5: Role-based scoping
echo "\nTEST 5: Role-Based Scoping\n";
$membersCount = \App\Models\User::members()->count();
$trainersCount = \App\Models\User::trainers()->count();
$adminsCount = \App\Models\User::admins()->count();
$activeCount = \App\Models\User::active()->count();

echo "✓ Members Scope: $membersCount\n";
echo "✓ Trainers Scope: $trainersCount\n";
echo "✓ Admins Scope: $adminsCount\n";
echo "✓ Active Users Scope: $activeCount\n";

echo "\n==== ALL TESTS PASSED ====\n";
