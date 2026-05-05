<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Trainer;
use Illuminate\Support\Facades\Hash;

echo "==== TRAINER CREATION TEST ====\n\n";

// Test: Create a new trainer
echo "TEST: Creating new trainer with auto user creation\n\n";

$testTrainerData = [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'johndoe' . time() . '@example.com',
    'specialization' => 'CrossFit',
    'phone' => '555-1234',
    'hourly_rate' => 75,
];

echo "Input Data:\n";
foreach ($testTrainerData as $key => $val) {
    echo "  $key: $val\n";
}

try {
    // Simulate the trainer creation process
    
    // Step 1: Create user if needed
    $user = User::create([
        'name' => trim($testTrainerData['first_name'] . ' ' . $testTrainerData['last_name']),
        'first_name' => $testTrainerData['first_name'],
        'last_name' => $testTrainerData['last_name'],
        'email' => $testTrainerData['email'],
        'password' => Hash::make('temp-' . uniqid()),
        'phone' => $testTrainerData['phone'],
        'specialization' => $testTrainerData['specialization'],
        'hourly_rate' => $testTrainerData['hourly_rate'],
        'role' => 'trainer',
        'is_active' => true,
    ]);
    
    echo "\n✓ User created: ID " . $user->id . "\n";
    
    // Step 2: Create trainer with user_id
    $trainerData = array_merge($testTrainerData, ['user_id' => $user->id]);
    $trainer = Trainer::create($trainerData);
    
    echo "✓ Trainer created: ID " . $trainer->id . "\n\n";
    
    // Step 3: Verify relationships
    $trainer->load('user');
    echo "Created Trainer:\n";
    echo "  ID: " . $trainer->id . "\n";
    echo "  Name: " . $trainer->first_name . " " . $trainer->last_name . "\n";
    echo "  Email: " . $trainer->email . "\n";
    echo "  User ID: " . $trainer->user_id . "\n";
    echo "  Linked User: " . ($trainer->user ? $trainer->user->name : "NOT LINKED") . "\n";
    
    // Cleanup
    $trainer->delete();
    $user->delete();
    
    echo "\n✓ Test cleanup completed\n";
    echo "\n==== TEST PASSED ====\n";
    
} catch (\Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
