<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Attendance;
use App\Models\User;
use App\Models\Trainer;

echo "=== Testing Attendance Response Structure ===\n\n";

// Get trainer user
$trainerUser = User::find(51); // The trainer we created
echo "1. Trainer User:\n";
echo "   - ID: {$trainerUser->id}\n";
echo "   - Email: {$trainerUser->email}\n";
echo "   - Role: {$trainerUser->role}\n";

// Get trainer profile
$trainer = $trainerUser->trainer;
echo "\n2. Trainer Profile:\n";
echo "   - ID: {$trainer->id}\n";
echo "   - Name: {$trainer->first_name} {$trainer->last_name}\n";

// Get attendance for trainer's classes
echo "\n3. Building Attendance Query:\n";

$query = Attendance::with('member', 'schedule.fitnessClass');
$query->whereHas('schedule.fitnessClass', function ($q) use ($trainer) {
    $q->where('trainer_id', $trainer->id);
});

$attendance = $query->limit(1)->get();

if ($attendance->isEmpty()) {
    echo "   ✅ Query working but no attendance records for this trainer's classes\n";
} else {
    echo "   ✅ Found attendance record\n";
    $record = $attendance->first();
    
    echo "\n4. Sample Attendance Record:\n";
    echo json_encode([
        'id' => $record->id,
        'member_id' => $record->member_id,
        'member_name' => $record->member ? $record->member->first_name . ' ' . $record->member->last_name : null,
        'schedule_id' => $record->schedule_id,
        'schedule' => [
            'id' => $record->schedule?->id,
            'class_id' => $record->schedule?->class_id,
            'class_date' => $record->schedule?->class_date,
            'start_time' => $record->schedule?->start_time,
        ],
        'fitnessClass' => [
            'id' => $record->schedule?->fitnessClass?->id,
            'class_name' => $record->schedule?->fitnessClass?->class_name,
            'trainer_id' => $record->schedule?->fitnessClass?->trainer_id,
        ],
        'attendance_status' => $record->attendance_status,
    ], JSON_PRETTY_PRINT) . "\n";
}
