<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\ClassSchedule;
use App\Models\FitnessClass;

echo "=== Creating Sample Schedules for Trainer's Classes ===\n\n";

// Get trainer's classes
$classes = FitnessClass::where('trainer_id', 23)->get();

echo "Found " . $classes->count() . " trainer classes\n\n";

foreach ($classes as $class) {
    echo "Creating schedules for: {$class->class_name} (ID: {$class->id})\n";
    
    // Create schedules for next 7 days, 2 per day
    $startDate = now()->toDateString();
    $schedules = [];
    
    for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
        $date = now()->addDays($dayOffset)->toDateString();
        
        // Morning class at 09:00
        $schedules[] = [
            'class_id' => $class->id,
            'class_date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'duration' => 60,
        ];
        
        // Evening class at 18:00
        $schedules[] = [
            'class_id' => $class->id,
            'class_date' => $date,
            'start_time' => '18:00:00',
            'end_time' => '19:00:00',
            'duration' => 60,
        ];
    }
    
    // Create all schedules
    foreach ($schedules as $schedule) {
        try {
            ClassSchedule::create($schedule);
            echo "  ✓ Created schedule for {$schedule['class_date']} at {$schedule['start_time']}\n";
        } catch (\Exception $e) {
            echo "  ✗ Error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
}

echo "✅ Schedules created! Trainer should now see the schedules section populated.\n";
