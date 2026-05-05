<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\ClassSchedule;
use App\Models\FitnessClass;
use Carbon\Carbon;

try {
    echo "=== Creating Sample Class Schedules ===\n\n";
    
    // Get all classes
    $classes = FitnessClass::all();
    
    if ($classes->count() === 0) {
        echo "❌ No fitness classes found\n";
        exit(1);
    }
    
    echo "Found " . $classes->count() . " classes\n\n";
    
    $created = 0;
    $today = now();
    
    foreach ($classes as $class) {
        // Create schedules for the next 7 days, 2 per day
        for ($i = 0; $i < 7; $i++) {
            $date = $today->clone()->addDays($i);
            
            // Morning class at 9:00 AM
            try {
                ClassSchedule::create([
                    'class_id' => $class->id,
                    'class_date' => $date->toDateString(),
                    'start_time' => '09:00',
                    'end_time' => '10:00',
                    'max_participants' => $class->max_participants ?? 20,
                ]);
                echo "✓ Created schedule for {$class->class_name} on {$date->toDateString()} at 09:00\n";
                $created++;
            } catch (\Exception $e) {
                // Might already exist, skip
            }
            
            // Evening class at 6:00 PM
            try {
                ClassSchedule::create([
                    'class_id' => $class->id,
                    'class_date' => $date->toDateString(),
                    'start_time' => '18:00',
                    'end_time' => '19:00',
                    'max_participants' => $class->max_participants ?? 20,
                ]);
                echo "✓ Created schedule for {$class->class_name} on {$date->toDateString()} at 18:00\n";
                $created++;
            } catch (\Exception $e) {
                // Might already exist, skip
            }
        }
    }
    
    echo "\n✅ Created $created class schedules\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
