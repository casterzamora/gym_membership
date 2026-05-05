<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\FitnessClass;
use App\Models\ClassSchedule;

try {
    echo "=== Fixing Yoga Class Issues ===\n\n";
    
    $class = FitnessClass::find(20); // ID from our previous check
    
    if ($class) {
        echo "Before:\n";
        echo "  Name: " . $class->class_name . "\n";
        echo "  Schedules:\n";
        foreach ($class->schedules as $sched) {
            echo "    - Time: " . $sched->start_time . ", Recurrence: " . $sched->recurrence_type . "\n";
        }
        
        // Fix the class name
        $class->update(['class_name' => 'Yoga']);
        
        // Fix all schedules for this class
        foreach ($class->schedules as $sched) {
            $sched->update([
                'start_time' => '10:00',  // Change from 22:15 to 10:00 AM
                'end_time' => '11:00',
                'recurrence_type' => 'weekly'  // Change from monthly to weekly
            ]);
        }
        
        // Refresh
        $class = FitnessClass::find(20);
        
        echo "\nAfter:\n";
        echo "  Name: " . $class->class_name . "\n";
        echo "  Schedules:\n";
        foreach ($class->schedules as $sched) {
            echo "    - Time: " . $sched->start_time . ", Recurrence: " . $sched->recurrence_type . "\n";
        }
        
        echo "\n✓ Yoga class fixed!\n";
    } else {
        echo "Class not found\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
