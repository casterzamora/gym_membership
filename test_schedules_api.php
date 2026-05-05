<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\ClassSchedule;
use App\Models\FitnessClass;
use App\Models\Trainer;

echo "=== Testing Schedule Data ===\n\n";

$trainerId = 23; // Our test trainer
$trainer = Trainer::find($trainerId);

echo "1. Trainer: {$trainer->first_name} {$trainer->last_name}\n";
echo "   ID: {$trainer->id}\n\n";

// Get trainer's classes
echo "2. Trainer's Classes:\n";
$classes = $trainer->classes;
echo "   Count: " . $classes->count() . "\n";

foreach ($classes as $class) {
    echo "   - {$class->class_name} (ID: {$class->id})\n";
}

echo "\n3. Class Schedules:\n";
$schedules = ClassSchedule::all();
echo "   Total schedules in DB: " . $schedules->count() . "\n";

// Filter for trainer's classes
$trainerSchedules = ClassSchedule::whereIn('class_id', $classes->pluck('id'))->get();
echo "   Schedules for trainer's classes: " . $trainerSchedules->count() . "\n";

if ($trainerSchedules->count() > 0) {
    echo "   Sample schedules:\n";
    foreach ($trainerSchedules->take(3) as $s) {
        echo "      - {$s->class_date} at {$s->start_time}-{$s->end_time}\n";
    }
}

echo "\n✅ Schedule data should be displayed in trainer's schedule page\n";
