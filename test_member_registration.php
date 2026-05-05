<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use App\Models\Member;
use Illuminate\Support\Facades\Hash;

try {
    echo "=== Testing Member Registration & Login ===\n\n";
    
    // Create a test member
    $testEmail = 'testmember' . time() . '@gym.com';
    $testPassword = 'TestPass123!';
    
    echo "1. Creating user...\n";
    $user = User::create([
        'name' => 'Test Member',
        'first_name' => 'Test',
        'last_name' => 'Member',
        'email' => $testEmail,
        'password' => Hash::make($testPassword),
        'phone' => '1234567890',
        'role' => 'member',
        'is_active' => true,
    ]);
    echo "   ✓ User created (ID: {$user->id})\n";
    
    echo "\n2. Creating member profile...\n";
    $member = Member::create([
        'user_id' => $user->id,
        'first_name' => 'Test',
        'last_name' => 'Member',
        'email' => $testEmail,
        'phone' => '1234567890',
        'date_of_birth' => null,
        'plan_id' => 1, // Assuming plan 1 exists
        'fitness_goal' => null,
        'health_notes' => null,
        'registration_type' => 'standard',
        'membership_start' => now()->toDateString(),
        'membership_end' => now()->addMonths(3)->toDateString(),
        'membership_status' => 'active',
    ]);
    echo "   ✓ Member profile created (ID: {$member->id})\n";
    
    echo "\n3. Verifying relationship...\n";
    $userFromDb = User::find($user->id);
    $memberFromDb = $userFromDb->member;
    
    if ($memberFromDb) {
        echo "   ✓ Member relationship works\n";
        echo "     - Member ID: {$memberFromDb->id}\n";
        echo "     - Status: {$memberFromDb->membership_status}\n";
    } else {
        echo "   ❌ Member relationship is NULL!\n";
    }
    
    echo "\n4. Testing login check...\n";
    $passwordMatches = Hash::check($testPassword, $user->password);
    echo "   - Password match: " . ($passwordMatches ? 'YES' : 'NO') . "\n";
    echo "   - User is active: " . ($user->is_active ? 'YES' : 'NO') . "\n";
    echo "   - User role: {$user->role}\n";
    
    $member = $user->member;
    if (!$member) {
        echo "   ❌ ERROR: Member profile not found!\n";
    } elseif ($member->membership_status !== 'active') {
        echo "   ⚠️  WARNING: Membership status is '{$member->membership_status}' (not 'active')\n";
    } else {
        echo "   ✓ Member can login successfully\n";
    }
    
    echo "\n✅ Test completed\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
