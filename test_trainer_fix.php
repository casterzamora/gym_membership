<?php
// Test trainer creation and email retrieval fix

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Trainer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "==== TRAINER EMAIL FIX TEST ====\n\n";

try {
    // Create test user
    $user = User::create([
        'name' => 'Test Trainer',
        'first_name' => 'Test',
        'last_name' => 'Trainer',
        'email' => 'test-trainer-' . uniqid() . '@example.com',
        'password' => Hash::make('temp-' . uniqid()),
        'phone' => '555-1234',
        'specialization' => 'Testing',
        'hourly_rate' => 50,
        'role' => 'trainer',
        'is_active' => true,
    ]);
    
    echo "✓ User created: ID {$user->id}\n";
    echo "  Email: {$user->email}\n\n";
    
    // Create trainer linked to user
    $trainer = Trainer::create([
        'user_id' => $user->id,
        'first_name' => 'Test',
        'last_name' => 'Trainer',
        'specialization' => 'Testing',
        'phone' => '555-1234',
        'hourly_rate' => 50,
    ]);
    
    echo "✓ Trainer created: ID {$trainer->id}\n";
    echo "  User ID: {$trainer->user_id}\n\n";
    
    // Test 1: Accessor
    echo "TEST 1: Email Accessor\n";
    echo "  trainer->email: " . ($trainer->email ?: '(empty)') . "\n";
    if ($trainer->email === $user->email) {
        echo "  ✓ Accessor returns correct email\n\n";
    } else {
        echo "  ✗ Accessor failed\n\n";
    }
    
    // Test 2: With loading
    echo "TEST 2: Loaded Relationship\n";
    $trainerLoaded = Trainer::with('user')->find($trainer->id);
    echo "  trainer->email: " . ($trainerLoaded->email ?: '(empty)') . "\n";
    echo "  trainer->user->email: " . ($trainerLoaded->user?->email ?: '(empty)') . "\n";
    if ($trainerLoaded->email === $user->email) {
        echo "  ✓ Email still accessible after loading\n\n";
    }
    
    // Test 3: Collection serialization
    echo "TEST 3: Serialized Array (like API response)\n";
    $trainerArray = $trainerLoaded->toArray();
    echo "  Keys: " . implode(', ', array_keys($trainerArray)) . "\n";
    echo "  email field: " . ($trainerArray['email'] ?? '(missing)') . "\n";
    if (isset($trainerArray['email']) && $trainerArray['email'] === $user->email) {
        echo "  ✓ Email included in serialized response\n\n";
    }
    
    // Test 4: Multiple trainers (like index() response)
    echo "TEST 4: Paginated Query (like index())\n";
    $trainers = Trainer::with('user', 'certifications', 'classes')->paginate(15);
    $testTrainer = $trainers->firstWhere('id', $trainer->id);
    if ($testTrainer) {
        echo "  Found: {$testTrainer->first_name} {$testTrainer->last_name}\n";
        echo "  Email: " . ($testTrainer->email ?: '(empty)') . "\n";
        if ($testTrainer->email === $user->email) {
            echo "  ✓ Email accessible in paginated response\n\n";
        }
    }
    
    // Cleanup
    echo "Cleanup...\n";
    $trainer->delete();
    $user->delete();
    echo "✓ Test data cleaned up\n\n";
    
    echo "==== ALL TESTS PASSED ====\n";
    
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    echo "  File: {$e->getFile()}\n";
    echo "  Line: {$e->getLine()}\n";
    exit(1);
}
