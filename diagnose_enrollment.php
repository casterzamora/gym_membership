<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Trainer;
use App\Models\FitnessClass;

echo "=== ENROLLMENT DIAGNOSIS ===\n\n";

// Find a trainer user (assume logged-in trainer has trainer_id = 6 based on earlier logs)
$trainerId = 6;
$trainer = Trainer::find($trainerId);

if (!$trainer) {
    echo "❌ Trainer ID $trainerId not found\n";
    exit;
}

echo "✅ Found Trainer ID $trainerId\n";
echo "   - Name: {$trainer->first_name} {$trainer->last_name}\n";
echo "   - User ID: {$trainer->user_id}\n";

$user = $trainer->user;
if (!$user) {
    echo "❌ User not linked to trainer\n";
} else {
    echo "✅ User ID: {$user->id}, Email: {$user->email}, Role: {$user->role}\n";
}

// Show classes assigned to this trainer
echo "\n--- Classes Assigned to Trainer $trainerId ---\n";
$classes = FitnessClass::where('trainer_id', $trainerId)->get();
if ($classes->isEmpty()) {
    echo "⚠️  NO CLASSES ASSIGNED TO THIS TRAINER\n";
} else {
    foreach ($classes as $class) {
        echo "  Class ID {$class->id}: {$class->class_name} (trainer_id={$class->trainer_id})\n";
        
        // Check schedules for this class
        $schedules = \App\Models\ClassSchedule::where('class_id', $class->id)->get();
        echo "    Schedules: " . $schedules->count() . "\n";
        foreach ($schedules as $sched) {
            echo "      - {$sched->class_date} {$sched->start_time}\n";
        }
    }
}

// Show ALL trainers and their classes
echo "\n--- All Trainers & Their Classes ---\n";
$allTrainers = Trainer::with('classes')->get();
foreach ($allTrainers as $t) {
    echo "Trainer ID {$t->id}: {$t->first_name} {$t->last_name} (user_id={$t->user_id})\n";
    if ($t->classes->isEmpty()) {
        echo "  → No classes\n";
    } else {
        foreach ($t->classes as $c) {
            echo "  → Class {$c->id}: {$c->class_name}\n";
        }
    }
}

// Show class to trainer mapping
echo "\n--- All Classes & Their Assigned Trainers ---\n";
$allClasses = FitnessClass::with('trainer')->get();
foreach ($allClasses as $class) {
    $trainerName = $class->trainer ? "{$class->trainer->first_name} {$class->trainer->last_name}" : "UNASSIGNED";
    echo "Class ID {$class->id} ({$class->class_name}): trainer_id={$class->trainer_id} ($trainerName)\n";
}
