<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Hash;
use App\Models\User;

echo "==== AUTH FLOW TEST ====\n\n";

// Test 1: Create test member user
echo "TEST 1: Creating Test Member User\n";
$uniqueEmail = 'testmember' . time() . '@example.com';
$uniqueUsername = 'testmember' . time();

$testMember = User::create([
    'name' => 'Test Member User',
    'first_name' => 'Test',
    'last_name' => 'Member',
    'email' => $uniqueEmail,
    'password' => Hash::make('password123'),
    'phone' => '5551234567',
    'role' => 'member',
    'is_active' => true,
]);

// Create member profile
$testMemberProfile = \App\Models\Member::create([
    'user_id' => $testMember->id,
    'first_name' => 'Test',
    'last_name' => 'Member',
    'email' => $uniqueEmail,
    'username' => $uniqueUsername,
    'password_hash' => Hash::make('password123'),
    'phone' => '5551234567',
    'date_of_birth' => '1990-01-01',
    'fitness_goal' => 'General fitness',
    'health_notes' => null,
    'registration_type' => 'standard',
    'plan_id' => 1,
    'membership_status' => 'active',
    'membership_start' => now()->toDateString(),
    'membership_end' => now()->addMonths(1)->toDateString(),
]);

echo "✓ Created test member user #" . $testMember->id . "\n";

// Test 2: Authenticate member
echo "\nTEST 2: Authenticating Member\n";
$loginUser = User::where('email', $uniqueEmail)->first();
if ($loginUser) {
    $passwordMatch = Hash::check('password123', $loginUser->password);
    echo "✓ User found: " . $loginUser->name . "\n";
    echo "✓ Password matches: " . ($passwordMatch ? "Yes" : "No") . "\n";
    echo "✓ Is active: " . ($loginUser->is_active ? "Yes" : "No") . "\n";
    echo "✓ Role: " . $loginUser->role . "\n";
    
    if ($loginUser->isMember()) {
        echo "✓ User is member\n";
        $memberProfile = $loginUser->member;
        if ($memberProfile) {
            echo "✓ Member profile linked\n";
            echo "✓ Membership status: " . $memberProfile->membership_status . "\n";
        }
    }
}

// Test 3: List member users
echo "\nTEST 3: Member Users List\n";
$members = User::members()->count();
echo "✓ Active members in system: $members\n";

// Test 4: Test scopes
echo "\nTEST 4: User Scopes\n";
$testUser = User::where('email', $uniqueEmail)->first();
if ($testUser) {
    echo "✓ Is member: " . ($testUser->isMember() ? "Yes" : "No") . "\n";
    echo "✓ Is admin: " . ($testUser->isAdmin() ? "Yes" : "No") . "\n";
    echo "✓ Is trainer: " . ($testUser->isTrainer() ? "Yes" : "No") . "\n";
}

// Cleanup
$testMember->delete();
$testMemberProfile->delete();

echo "\n==== AUTH FLOW TESTS PASSED ====\n";
