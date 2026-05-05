<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use App\Models\Trainer;
use Illuminate\Support\Facades\Hash;

try {
    echo "=== Testing Trainer Creation with Password ===\n\n";
    
    // Test 1: Manually create trainer (simulating what the controller does)
    echo "TEST 1: Creating trainer with password\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $email = 'johntrainer' . time() . '@gym.com';
    $password = 'SecurePass123!';
    
    // Create user account for trainer
    $user = User::create([
        'name' => 'John Trainer',
        'first_name' => 'John',
        'last_name' => 'Trainer',
        'email' => $email,
        'password' => Hash::make($password),
        'phone' => '555-1234',
        'specialization' => 'CrossFit',
        'hourly_rate' => '75',
        'role' => 'trainer',
        'is_active' => true,
    ]);
    
    echo "✓ User created for trainer\n";
    echo "  - Email: {$user->email}\n";
    echo "  - ID: {$user->id}\n";
    
    // Create trainer profile
    $trainer = Trainer::create([
        'user_id' => $user->id,
        'first_name' => 'John',
        'last_name' => 'Trainer',
        'specialization' => 'CrossFit',
        'phone' => '555-1234',
        'hourly_rate' => '75',
    ]);
    
    echo "✓ Trainer profile created\n";
    echo "  - Trainer ID: {$trainer->id}\n";
    
    // Test 2: Verify password works
    echo "\n\nTEST 2: Verify password authentication\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $userFromDb = User::find($user->id);
    $passwordMatches = Hash::check($password, $userFromDb->password);
    
    if ($passwordMatches) {
        echo "✅ Password verification successful\n";
        echo "  - Password matches: YES\n";
    } else {
        echo "❌ Password verification failed\n";
    }
    
    // Test 3: Simulate login
    echo "\n\nTEST 3: Simulate trainer login\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $loginEmail = $email;
    $loginPassword = $password;
    
    $loginUser = User::where('email', $loginEmail)->first();
    if (!$loginUser) {
        echo "❌ User not found\n";
        exit(1);
    }
    
    if (!Hash::check($loginPassword, $loginUser->password)) {
        echo "❌ Password incorrect\n";
        exit(1);
    }
    
    if (!$loginUser->is_active) {
        echo "❌ User account inactive\n";
        exit(1);
    }
    
    $token = $loginUser->createToken('api-token')->plainTextToken;
    echo "✅ Trainer login successful\n";
    echo "  - Email: {$loginUser->email}\n";
    echo "  - Role: {$loginUser->role}\n";
    echo "  - Token generated: YES\n";
    
    echo "\n✅ All tests passed!\n";
    echo "\nTrainer Login Credentials:\n";
    echo "  Email: $email\n";
    echo "  Password: $password\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
