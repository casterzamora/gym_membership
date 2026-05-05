<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

use App\Models\ClassSchedule;

echo "=== Testing Schedule 31 ===\n";

$schedule = ClassSchedule::find(31);

if (!$schedule) {
    echo "❌ Schedule not found\n";
    exit(1);
}

echo "✅ Schedule found\n";
echo "   ID: {$schedule->id}\n";
echo "   Class ID: {$schedule->class_id}\n";
echo "   Start Time: {$schedule->start_time}\n";
echo "   End Time: {$schedule->end_time}\n";
echo "   Recurrence: " . ($schedule->recurrence_type ?? 'none') . "\n\n";

echo "=== Testing Relationships ===\n";

try {
    $class = $schedule->fitnessClass;
    if ($class) {
        echo "✅ Fitness Class loaded\n";
        echo "   ID: {$class->id}\n";
        echo "   Name: {$class->class_name}\n";
        
        $trainer = $class->trainer;
        if ($trainer) {
            echo "✅ Trainer loaded\n";
            echo "   ID: {$trainer->id}\n";
        } else {
            echo "❌ Trainer not found\n";
        }
    } else {
        echo "❌ Fitness Class is null\n";
    }
} catch (\Exception $e) {
    echo "❌ Error loading fitnessClass: {$e->getMessage()}\n";
}

echo "\n=== Testing toArray() ===\n";
try {
    $schedule->load('fitnessClass.trainer', 'attendances');
    $data = $schedule->toArray();
    echo "✅ toArray() successful\n";
    echo "Array keys: " . implode(', ', array_keys($data)) . "\n";
    echo "start_time in array: " . (isset($data['start_time']) ? $data['start_time'] : 'MISSING') . "\n";
    echo "end_time in array: " . (isset($data['end_time']) ? $data['end_time'] : 'MISSING') . "\n";
} catch (\Exception $e) {
    echo "❌ Error in toArray(): {$e->getMessage()}\n";
}
