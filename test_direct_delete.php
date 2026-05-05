<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FitnessClass;
use App\Models\Trainer;

echo "=== DIRECT MODEL DELETE TEST ===\n\n";

// Get a trainer to create a test class
$trainer = Trainer::first();
if (!$trainer) {
    echo "No trainers found in database\n";
    exit(1);
}

// Create a test class
$testClass = FitnessClass::create([
    'class_name' => 'Test Delete - ' . now()->timestamp,
    'description' => 'Test class for deletion',
    'trainer_id' => $trainer->id,
    'max_participants' => 15,
]);

echo "Created test class:\n";
echo "- ID: {$testClass->id}\n";
echo "- Name: {$testClass->class_name}\n";
echo "- Trainer ID: {$testClass->trainer_id}\n\n";

// Check it exists
$exists1 = FitnessClass::find($testClass->id);
echo "Class exists after creation? " . ($exists1 ? 'YES' : 'NO') . "\n\n";

// Try to delete
echo "Attempting to delete class {$testClass->id}...\n";
try {
    $deleted = $testClass->delete();
    echo "Delete result: " . ($deleted ? 'SUCCESS' : 'FAILED') . "\n";
    
    // Check if it still exists
    $exists2 = FitnessClass::find($testClass->id);
    echo "Class still exists after delete? " . ($exists2 ? 'YES (ERROR)' : 'NO (Correct)') . "\n";
} catch (\Exception $e) {
    echo "Exception during delete: {$e->getMessage()}\n";
    echo "Trace:\n{$e->getTraceAsString()}\n";
}

echo "\n✓ Direct model delete test completed\n";
