<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\ClassSchedule;
use App\Models\FitnessClass;

try {
    echo "=== Class Schedule Status ===\n\n";
    
    $today = now()->toDateString();
    echo "Today's date: $today\n\n";
    
    // Check total schedules
    $totalSchedules = ClassSchedule::count();
    echo "Total class schedules in DB: $totalSchedules\n";
    
    // Check upcoming schedules
    $upcomingSchedules = ClassSchedule::where('class_date', '>=', $today)
        ->orderBy('class_date', 'asc')
        ->get();
    
    echo "Upcoming schedules (from today onwards): " . $upcomingSchedules->count() . "\n\n";
    
    if ($upcomingSchedules->count() > 0) {
        echo "Upcoming Classes:\n";
        foreach ($upcomingSchedules->take(10) as $schedule) {
            $class = FitnessClass::find($schedule->class_id);
            echo "  - {$class->class_name} on {$schedule->class_date} at {$schedule->start_time}\n";
        }
    } else {
        echo "❌ No upcoming schedules found!\n";
        echo "You need to create class schedules in the past or future.\n\n";
        
        // Show all schedules that exist
        $allSchedules = ClassSchedule::orderBy('class_date', 'desc')
            ->limit(5)
            ->get();
        
        if ($allSchedules->count() > 0) {
            echo "Recent schedules:\n";
            foreach ($allSchedules as $schedule) {
                $class = FitnessClass::find($schedule->class_id);
                echo "  - {$class->class_name} on {$schedule->class_date} at {$schedule->start_time}\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
