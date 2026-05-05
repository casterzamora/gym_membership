<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Trainer;
use App\Models\Member;
use App\Models\ClassSchedule;

echo "=== SIMULATE ENROLLMENT TEST ===\n\n";

// Authenticated trainer
$user = User::find(29);
echo "Authenticated User:\n";
echo "  ID: {$user->id}, Email: {$user->email}, Role: {$user->role}\n";
echo "  Linked Trainer ID: {$user->trainer->id}\n\n";

// Get the trainer from user
$trainer = $user->trainer;
echo "Trainer:\n";
echo "  ID: {$trainer->id}\n";
$classIds = $trainer->classes->pluck('id')->toArray();
echo "  Trainer's classes: " . json_encode($classIds) . "\n\n";

// Check schedule 4 (which was in the failed request)
echo "Schedule 4 Details:\n";
$schedule4 = ClassSchedule::find(4);
if ($schedule4) {
    echo "  Found: class_id={$schedule4->class_id}, date={$schedule4->class_date}, time={$schedule4->start_time}\n";
    $class = $schedule4->fitnessClass;
    echo "  Class: ID {$class->id}, Name '{$class->class_name}', trainer_id={$class->trainer_id}\n";
    
    // Check ownership
    $ownershipMatch = (int)$class->trainer_id === (int)$trainer->id;
    echo "  Ownership check: trainer_id {$trainer->id} === class.trainer_id {$class->trainer_id} ? " . ($ownershipMatch ? "✅ YES" : "❌ NO") . "\n";
} else {
    echo "  NOT FOUND\n";
}

// Try to pick a member that exists
echo "\n--- Available Members to Enroll ---\n";
$members = Member::limit(5)->get();
foreach ($members as $m) {
    $hasUser = $m->user ? "✓" : "✗";
    echo "  Member {$m->id}: {$m->first_name} {$m->last_name} (User {$hasUser})\n";
}

// Try to show schedules for class 8 (which belongs to trainer 6)
echo "\n--- Schedules for Class 8 (trainer 6's class) ---\n";
$class8Schedules = ClassSchedule::where('class_id', 8)->get();
foreach ($class8Schedules as $s) {
    echo "  Schedule {$s->id}: {$s->class_date} {$s->start_time}\n";
}
