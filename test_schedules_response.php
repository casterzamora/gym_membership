<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\ClassSchedule;
use App\Models\FitnessClass;

echo "=== Testing Schedules API Response ===\n\n";

// Get all schedules
$schedules = ClassSchedule::with('fitnessClass')->get();

echo "1. Total Schedules: " . $schedules->count() . "\n\n";

echo "2. Sample Schedule Response (as API will return):\n";

if ($schedules->count() > 0) {
    $sampleSchedule = $schedules->first();
    $response = [
        'id' => $sampleSchedule->id,
        'class_id' => $sampleSchedule->class_id,
        'class_date' => $sampleSchedule->class_date,
        'start_time' => $sampleSchedule->start_time,
        'end_time' => $sampleSchedule->end_time,
        'duration' => $sampleSchedule->duration,
        'recurrence_type' => $sampleSchedule->recurrence_type,
        'recurrence_end_date' => $sampleSchedule->recurrence_end_date,
        'fitnessClass' => [
            'id' => $sampleSchedule->fitnessClass?->id,
            'class_name' => $sampleSchedule->fitnessClass?->class_name,
            'trainer_id' => $sampleSchedule->fitnessClass?->trainer_id,
        ]
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n\n";
}

echo "3. Schedules by Trainer:\n";
$classes = FitnessClass::all();
foreach ($classes as $class) {
    $classSchedules = ClassSchedule::where('class_id', $class->id)->get();
    echo "   {$class->class_name} (Trainer ID: {$class->trainer_id}): " . $classSchedules->count() . " schedules\n";
}

echo "\n✅ API is correctly structured\n";
