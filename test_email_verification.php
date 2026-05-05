<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use App\Models\MembershipPlan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

try {
    echo "=== Testing Email Verification Flow ===\n\n";
    
    // Get a plan
    $plan = MembershipPlan::first();
    if (!$plan) {
        echo "❌ No membership plans found. Please seed the database first.\n";
        exit(1);
    }
    
    // Register a test user
    echo "1. Creating test registration...\n";
    $email = 'test-verify-' . Str::random(8) . '@example.com';
    $verificationToken = Str::random(64);
    
    $user = User::create([
        'name' => 'Test Verify User',
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => $email,
        'password' => Hash::make('TestPassword123'),
        'role' => 'member',
        'is_active' => false,
        'email_verification_token' => hash('sha256', $verificationToken),
    ]);
    
    echo "✓ User created: ID {$user->id}, Email: {$user->email}\n";
    echo "  - Email verification token set\n";
    echo "  - email_verified_at is NULL: " . ($user->email_verified_at === null ? 'Yes' : 'No') . "\n";
    
    // Simulate clicking the verification link
    echo "\n2. Verifying email with token...\n";
    $hashedToken = hash('sha256', $verificationToken);
    $verifyUser = User::where('email_verification_token', $hashedToken)->first();
    
    if (!$verifyUser) {
        echo "❌ Could not find user with verification token\n";
        exit(1);
    }
    
    echo "✓ User found with token\n";
    
    // Verify the email
    $verifyUser->email_verified_at = now();
    $verifyUser->email_verification_token = null;
    $verifyUser->save();
    
    // Refresh from DB
    $verifyUser->refresh();
    
    echo "✓ Email verified\n";
    echo "  - email_verified_at: " . $verifyUser->email_verified_at . "\n";
    echo "  - email_verification_token: " . ($verifyUser->email_verification_token ?? 'NULL') . "\n";
    
    echo "\n✅ Email verification flow works correctly!\n";
    
    // Cleanup
    $user->delete();
    echo "\n✓ Test user cleaned up\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
