<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\ClassSchedule;
use App\Models\FitnessClass;

echo "=== Checking All Schedules by Class ===\n\n";

$classes = FitnessClass::all();
echo "Total classes: " . $classes->count() . "\n\n";

foreach ($classes as $class) {
    $scheduleCount = ClassSchedule::where('class_id', $class->id)->count();
    $trainer = $class->trainer;
    $trainerName = $trainer ? $trainer->first_name . ' ' . $trainer->last_name : 'N/A';
    
    echo "Class: {$class->class_name} (ID: {$class->id})\n";
    echo "  - Trainer: {$trainerName} (Trainer ID: {$class->trainer_id})\n";
    echo "  - Schedules: $scheduleCount\n";
    
    if ($scheduleCount > 0) {
        $schedule = ClassSchedule::where('class_id', $class->id)->first();
        echo "    First schedule: {$schedule->class_date} at {$schedule->start_time}\n";
    }
    echo "\n";
}
