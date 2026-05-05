<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Trainer;
use App\Models\User;
use App\Models\FitnessClass;

try {
    echo "=== All Trainers in System ===\n\n";
    
    $trainers = Trainer::all();
    foreach ($trainers as $trainer) {
        echo "Trainer ID: " . $trainer->id . "\n";
        echo "  User ID: " . $trainer->user_id . "\n";
        
        if ($trainer->user) {
            echo "  Name: " . $trainer->user->name . "\n";
            echo "  Email: " . $trainer->user->email . "\n";
        } else {
            echo "  ⚠️  NO USER ACCOUNT LINKED\n";
        }
        
        $classes = FitnessClass::where('trainer_id', $trainer->id)->get();
        echo "  Classes assigned: " . $classes->count() . "\n";
        foreach ($classes as $class) {
            echo "    - " . $class->class_name . " (ID: " . $class->id . ")\n";
        }
        echo "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
