<?php

// Quick validation test to debug schedule creation
require 'bootstrap/app.php';

use Illuminate\Support\Facades\Validator;

// Test data
$testData = [
    'class_id' => 1,
    'class_date' => '2026-04-24',
    'start_time' => '10:30',
    'end_time' => '11:30',
    'recurrence_type' => null,
    'recurrence_end_date' => null,
];

// Validation rules from the request
$rules = [
    'class_id' => 'required|exists:fitness_classes,id',
    'class_date' => 'required|date|after_or_equal:today',
    'start_time' => 'required|date_format:H:i',
    'end_time' => 'required|date_format:H:i|after:start_time',
    'recurrence_type' => 'nullable|string|max:50',
    'recurrence_end_date' => 'nullable|date|after:class_date',
];

echo "=== Schedule Validation Diagnostics ===\n\n";
echo "Current server time: " . date('Y-m-d H:i:s') . "\n";
echo "Current timezone: " . date_default_timezone_get() . "\n";
echo "App timezone: " . config('app.timezone') . "\n\n";

echo "Test data:\n";
echo json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

$validator = Validator::make($testData, $rules);

if ($validator->fails()) {
    echo "VALIDATION FAILED:\n";
    foreach ($validator->errors()->all() as $error) {
        echo "  - $error\n";
    }
} else {
    echo "VALIDATION PASSED!\n";
}

echo "\n=== Detailed Field Checks ===\n";
echo "class_id type: " . gettype($testData['class_id']) . " value: " . $testData['class_id'] . "\n";
echo "class_date format check: " . (preg_match('/^\d{4}-\d{2}-\d{2}$/', $testData['class_date']) ? 'VALID' : 'INVALID') . "\n";
echo "start_time format check: " . (preg_match('/^\d{1,2}:\d{2}$/', $testData['start_time']) ? 'VALID' : 'INVALID') . "\n";
echo "end_time format check: " . (preg_match('/^\d{1,2}:\d{2}$/', $testData['end_time']) ? 'VALID' : 'INVALID') . "\n";
echo "end_time after start_time: " . (strtotime($testData['end_time']) > strtotime($testData['start_time']) ? 'VALID' : 'INVALID') . "\n";

// Check if we can find a valid class_id
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');

// Try to find an actual class
try {
    $classId = \App\Models\FitnessClass::first()?->id;
    if ($classId) {
        echo "\nFirst class ID in database: $classId\n";
        echo "Using that as test class_id...\n";
        $testData['class_id'] = $classId;
        
        $validator2 = Validator::make($testData, $rules);
        echo "Validation with real class_id: " . ($validator2->fails() ? 'FAILED' : 'PASSED') . "\n";
        if ($validator2->fails()) {
            foreach ($validator2->errors()->all() as $error) {
                echo "  - $error\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "Could not check database: " . $e->getMessage() . "\n";
}
