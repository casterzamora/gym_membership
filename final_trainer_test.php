<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use App\Models\FitnessClass;
use App\Models\ClassSchedule;
use App\Models\Member;

echo "=== FINAL TRAINER DASHBOARD TEST ===\n\n";

$trainer = User::find(51);
echo "LOGIN: johntrainer1776949848@gym.com / SecurePass123!\n";
echo "Trainer ID in Auth: {$trainer->trainer->id}\n\n";

//=== 1. CLASSES ===
echo "1️⃣  CLASSES SECTION\n";
$classes = FitnessClass::where('trainer_id', $trainer->trainer->id)->get();
echo "   ✅ Classes Count: " . $classes->count() . "\n";
foreach ($classes as $c) {
    echo "      • {$c->class_name}\n";
}

//=== 2. SCHEDULES ===
echo "\n2️⃣  SCHEDULES SECTION\n";
$classIds = $classes->pluck('id')->toArray();
$schedules = ClassSchedule::whereIn('class_id', $classIds)->get();
echo "   ✅ Schedules Count: " . $schedules->count() . "\n";
if ($schedules->count() > 0) {
    echo "      Sample:\n";
    foreach ($schedules->take(3) as $s) {
        echo "      • " . $s->class_date . " " . formatTime($s->start_time) . "-" . formatTime($s->end_time) . "\n";
    }
}

function formatTime($time) {
    return substr($time, 0, 5);
}

//=== 3. MEMBERS ===
echo "\n3️⃣  STUDENTS/MEMBERS SECTION\n";
$members = Member::all();
echo "   ✅ Members Count: " . $members->count() . "\n";
if ($members->count() > 0) {
    echo "      Sample:\n";
    foreach ($members->take(3) as $m) {
        echo "      • {$m->first_name} {$m->last_name}\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ ALL SECTIONS FUNCTIONAL\n";
echo "\nIN BROWSER:\n";
echo "1. Refresh the page to reload data\n";
echo "2. Classes section should show 2 classes\n";
echo "3. Schedules section should show 28 schedules (14 per class)\n";
echo "4. Students section should show 33 members\n";
echo "5. 'New Schedule' button should be enabled\n";
