<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\FitnessClass;
use App\Models\Trainer;

try {
    echo "=== Yoga Class Details ===\n\n";
    
    $class = FitnessClass::where('class_name', 'like', '%yoga%')->first();
    
    if ($class) {
        echo "Class Name: " . $class->class_name . "\n";
        echo "Class ID: " . $class->id . "\n";
        echo "Trainer ID: " . $class->trainer_id . "\n";
        
        if ($class->trainer) {
            echo "Trainer Name: " . $class->trainer->name . "\n";
            echo "Trainer ID: " . $class->trainer->id . "\n";
            echo "Trainer Email: " . $class->trainer->user->email . "\n";
        } else {
            echo "⚠️  Trainer not assigned to this class!\n";
        }
        
        echo "Description: " . $class->description . "\n";
        echo "\nSchedules for this class:\n";
        
        foreach ($class->schedules as $sched) {
            echo "  - Schedule ID: " . $sched->id . "\n";
            echo "    Date: " . $sched->class_date . "\n";
            echo "    Time: " . $sched->start_time . " - " . $sched->end_time . "\n";
            echo "    Recurrence: " . $sched->recurrence_type . "\n";
        }
        
        echo "\n\nLooking for trainers to verify visibility:\n";
        $trainers = Trainer::with('user')->get();
        foreach ($trainers as $trainer) {
            $classCount = $trainer->fitnessClasses->count();
            echo "  - Trainer: " . $trainer->user->name . " (ID: " . $trainer->id . ") has $classCount classes\n";
            if ($trainer->fitnessClasses->contains($class)) {
                echo "    ✓ This trainer teaches the yoga class\n";
            }
        }
    } else {
        echo "❌ No yoga class found in database!\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
