<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

use App\Models\ClassSchedule;

echo "=== Testing Schedule Update Flow ===\n\n";

// Get a schedule to test with
$schedule = ClassSchedule::first();

if (!$schedule) {
    echo "❌ No schedules found in database\n";
    exit(1);
}

echo "✅ Found schedule to test:\n";
echo "   ID: {$schedule->id}\n";
echo "   Class ID: {$schedule->class_id}\n";
echo "   Date: {$schedule->class_date}\n";
echo "   Start Time (raw): {$schedule->start_time}\n";
echo "   End Time (raw): {$schedule->end_time}\n";
echo "   Recurrence: {$schedule->recurrence_type}\n\n";

// Test data format
echo "=== Testing Update Payload Format ===\n";
$testPayload = [
    'class_id' => $schedule->class_id,
    'class_date' => $schedule->class_date,
    'start_time' => '14:00',  // Change from original
    'end_time' => '15:00',    // Change from original
    'recurrence_type' => $schedule->recurrence_type,
];

echo "Test payload:\n";
echo json_encode($testPayload, JSON_PRETTY_PRINT) . "\n\n";

// Test validation
echo "=== Testing Validation ===\n";
try {
    $validator = \Illuminate\Support\Facades\Validator::make($testPayload, [
        'class_id' => 'sometimes|exists:fitness_classes,id',
        'class_date' => 'sometimes|date|after_or_equal:today',
        'start_time' => 'sometimes|date_format:H:i',
        'end_time' => 'sometimes|date_format:H:i|after:start_time',
        'recurrence_type' => 'nullable|string|max:50',
        'recurrence_end_date' => 'nullable|date|after:class_date',
    ]);
    
    if ($validator->fails()) {
        echo "❌ Validation failed:\n";
        foreach ($validator->errors()->all() as $error) {
            echo "   - $error\n";
        }
    } else {
        echo "✅ Validation passed\n";
    }
} catch (\Exception $e) {
    echo "❌ Validation error: {$e->getMessage()}\n";
}

echo "\n";

// Test actual update
echo "=== Testing Actual Update ===\n";
try {
    $updated = $schedule->update([
        'start_time' => '14:00',
        'end_time' => '15:00',
    ]);
    
    echo "✅ Update succeeded\n";
    
    // Refresh and verify
    $schedule->refresh();
    echo "   New start_time: {$schedule->start_time}\n";
    echo "   New end_time: {$schedule->end_time}\n";
    
    // Rollback the test change
    $schedule->update([
        'start_time' => '09:00',
        'end_time' => '10:00',
    ]);
    echo "✅ Rolled back test change\n";
} catch (\Exception $e) {
    echo "❌ Update failed: {$e->getMessage()}\n";
}
