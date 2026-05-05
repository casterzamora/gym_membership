<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Attendance;

echo "=== Testing Attendance API Response ===\n\n";

// Get sample attendance records
$attendance = Attendance::with('schedule', 'schedule.class', 'member')->limit(3)->get();

if ($attendance->isEmpty()) {
    echo "❌ No attendance records found\n";
    exit(1);
}

echo "✅ Found " . $attendance->count() . " attendance records\n\n";

echo "Sample Attendance Record:\n";
$record = $attendance->first();
echo json_encode([
    'id' => $record->id,
    'member_id' => $record->member_id,
    'schedule_id' => $record->schedule_id,
    'attendance_status' => $record->attendance_status,
    'schedule' => [
        'id' => $record->schedule?->id,
        'class_id' => $record->schedule?->class_id,
        'class_date' => $record->schedule?->class_date,
        'start_time' => $record->schedule?->start_time,
    ],
    'member' => [
        'id' => $record->member?->id,
        'first_name' => $record->member?->first_name,
        'last_name' => $record->member?->last_name,
    ]
], JSON_PRETTY_PRINT) . "\n";

// Check if we have attendance for a specific trainer's classes
echo "\n=== Checking Trainer's Students ===\n\n";

$trainerId = 23; // Our test trainer
$classes = \App\Models\FitnessClass::where('trainer_id', $trainerId)->pluck('id');

echo "Trainer ID: $trainerId\n";
echo "Their classes: " . implode(', ', $classes->toArray()) . "\n";

$trainerAttendance = Attendance::with('schedule', 'member')
    ->whereHas('schedule', function($q) use ($classes) {
        $q->whereIn('class_id', $classes);
    })
    ->get();

echo "Total attendance records for this trainer's classes: " . $trainerAttendance->count() . "\n";

if ($trainerAttendance->count() > 0) {
    $memberIds = $trainerAttendance->pluck('member_id')->unique();
    echo "Unique members: " . implode(', ', $memberIds->toArray()) . "\n";
}
