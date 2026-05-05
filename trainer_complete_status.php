<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\FitnessClass;
use App\Models\ClassSchedule;
use App\Models\Member;
use App\Models\Trainer;

echo "=== Trainer Dashboard Complete Status ===\n\n";

$trainerId = 23;
$trainer = Trainer::find($trainerId);

echo "Trainer: {$trainer->first_name} {$trainer->last_name}\n";
echo "Email: {$trainer->user?->email}\n";
echo "Trainer ID in Auth: {$trainer->id}\n\n";

// Classes
$classes = FitnessClass::where('trainer_id', $trainerId)->get();
echo "1. CLASSES SECTION:\n";
echo "   Classes: " . $classes->count() . "\n";
foreach ($classes as $c) {
    echo "      ✓ {$c->class_name}\n";
}

// Schedules
echo "\n2. SCHEDULES SECTION:\n";
$totalSchedules = 0;
foreach ($classes as $class) {
    $count = ClassSchedule::where('class_id', $class->id)->count();
    $totalSchedules += $count;
    echo "      ✓ {$class->class_name}: $count schedules\n";
}
echo "   Total: $totalSchedules schedules\n";

// Members
$members = Member::all();
echo "\n3. STUDENTS/MEMBERS SECTION:\n";
echo "   Members available: " . $members->count() . "\n";
echo "   Sample members:\n";
foreach ($members->take(5) as $m) {
    $name = "{$m->first_name} {$m->last_name}";
    echo "      ✓ $name\n";
}

echo "\n✅ ALL SECTIONS SHOULD NOW HAVE DATA!\n";
echo "\nRefresh your trainer dashboard to see:\n";
echo "   • Classes page - List of your classes with create button\n";
echo "   • Schedules page - " . $totalSchedules . " schedules\n";
echo "   • My Students page - " . $members->count() . " members\n";
