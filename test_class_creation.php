<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\FitnessClass;
use App\Models\Trainer;

echo "=== Testing Class Creation ===\n\n";

$trainerId = 23; // The trainer we just verified

// Check if trainer exists
$trainer = Trainer::find($trainerId);
if (!$trainer) {
    echo "❌ Trainer not found\n";
    exit(1);
}

echo "✅ Trainer found: {$trainer->first_name} {$trainer->last_name}\n\n";

// Test creating a class
$classData = [
    'class_name' => 'Test Class - ' . date('Y-m-d H:i:s'),
    'description' => 'Test class description',
    'max_participants' => 20,
    'difficulty_level' => 'Intermediate',
    'trainer_id' => $trainerId,
];

echo "📝 Creating class with data:\n";
echo json_encode($classData, JSON_PRETTY_PRINT) . "\n\n";

try {
    $class = FitnessClass::create($classData);
    echo "✅ Class created successfully!\n";
    echo "   - Class ID: {$class->id}\n";
    echo "   - Class Name: {$class->class_name}\n";
    echo "   - Trainer ID: {$class->trainer_id}\n";
} catch (\Exception $e) {
    echo "❌ Error creating class: " . $e->getMessage() . "\n";
}
