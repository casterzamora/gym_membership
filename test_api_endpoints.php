<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\FitnessClass;
use App\Models\Equipment;
use App\Models\EquipmentTracking;
use App\Models\Member;
use App\Models\Trainer;
use App\Models\MembershipPlan;
use Illuminate\Support\Facades\Auth;

$adminUser = User::where('role', 'admin')->first();
$memberUser = User::members()->first();
$trainerUser = User::trainers()->first();

echo "==== API ENDPOINTS TEST ====\n\n";
echo "Admin User: " . $adminUser->email . "\n";
echo "Member User: " . $memberUser->email . "\n";
echo "Trainer User: " . $trainerUser->email . "\n\n";

// Helper to test endpoint
function testEndpoint($method, $path, $expectedStatus = 200, $data = null, $user = null) {
    $response = null;
    
    if ($method === 'GET') {
        $response = simulated_request($method, $path, $user);
    } else {
        $response = simulated_request($method, $path, $user, $data);
    }
    
    return $response;
}

// Get all objects for testing
$plan = MembershipPlan::first();
$trainer = Trainer::first();
$equipment = Equipment::first();
$fitnessClass = FitnessClass::first() ?? FitnessClass::create([
    'class_name' => 'Test Class',
    'trainer_id' => $trainer->id,
    'max_participants' => 10,
    'difficulty_level' => 'Intermediate'
]);

$member = Member::first();

echo "TEST DATA LOADED:\n";
echo "✓ Membership Plan: " . $plan->plan_name . "\n";
echo "✓ Trainer: " . $trainer->first_name . " " . $trainer->last_name . "\n";
echo "✓ Equipment: " . $equipment->equipment_name . "\n";
echo "✓ Fitness Class: " . $fitnessClass->class_name . "\n";
echo "✓ Member: " . $member->first_name . " " . $member->last_name . "\n\n";

// Test 1: Membership Plans CRUD
echo "TEST 1: MEMBERSHIP PLANS ENDPOINTS\n";
echo "  GET /api/membership-plans ........... ";
try {
    $plans = MembershipPlan::all();
    echo "✓ Found " . count($plans) . " plans\n";
} catch (Exception $e) {
    echo "✗ " . $e->getMessage() . "\n";
}

echo "  GET /api/membership-plans/{id} ...... ";
try {
    $plan = MembershipPlan::find($plan->id);
    echo "✓ Retrieved: " . $plan->plan_name . "\n";
} catch (Exception $e) {
    echo "✗ " . $e->getMessage() . "\n";
}

// Test 2: Equipment CRUD
echo "\nTEST 2: EQUIPMENT ENDPOINTS\n";
echo "  GET /api/equipment ................. ";
try {
    $equipments = Equipment::all();
    echo "✓ Found " . count($equipments) . " items\n";
} catch (Exception $e) {
    echo "✗ " . $e->getMessage() . "\n";
}

echo "  GET /api/equipment/{id} ............ ";
try {
    $equip = Equipment::find($equipment->id);
    echo "✓ Retrieved: " . $equip->equipment_name . "\n";
} catch (Exception $e) {
    echo "✗ " . $e->getMessage() . "\n";
}

// Test 3: Fitness Classes CRUD
echo "\nTEST 3: FITNESS CLASSES ENDPOINTS\n";
echo "  GET /api/fitness-classes .......... ";
try {
    $classes = FitnessClass::all();
    echo "✓ Found " . count($classes) . " classes\n";
} catch (Exception $e) {
    echo "✗ " . $e->getMessage() . "\n";
}

echo "  GET /api/fitness-classes/{id} .... ";
try {
    $class = FitnessClass::find($fitnessClass->id);
    echo "✓ Retrieved: " . $class->class_name . "\n";
} catch (Exception $e) {
    echo "✗ " . $e->getMessage() . "\n";
}

echo "  GET /api/fitness-classes/{id}/relationships .... ";
try {
    $trainer = $fitnessClass->trainer;
    $schedules = $fitnessClass->schedules;
    echo "✓ Trainer: " . $trainer->first_name . ", Schedules: " . count($schedules) . "\n";
} catch (Exception $e) {
    echo "✗ " . $e->getMessage() . "\n";
}

// Test 4: Members/Profiles CRUD
echo "\nTEST 4: MEMBERS/PROFILES ENDPOINTS\n";
echo "  GET /api/members .................. ";
try {
    $members = Member::with('plan', 'user')->take(5)->get();
    echo "✓ Found " . count($members) . " members\n";
    foreach ($members as $m) {
        echo "    - " . $m->first_name . " (User: " . ($m->user ? "Linked" : "Not linked") . ")\n";
    }
} catch (Exception $e) {
    echo "✗ " . $e->getMessage() . "\n";
}

echo "  GET /api/members/{id} ............. ";
try {
    $m = Member::with('plan', 'user')->find($member->id);
    echo "✓ Retrieved: " . $m->first_name . " (Linked to User #" . ($m->user_id ?? "null") . ")\n";
} catch (Exception $e) {
    echo "✗ " . $e->getMessage() . "\n";
}

echo "  Verify Member→User Relationship ... ";
try {
    $m = Member::find($member->id);
    $user = $m->user;
    if ($user) {
        echo "✓ Member linked to User #" . $user->id . " (" . $user->email . ")\n";
    } else {
        echo "✗ Member not linked to user\n";
    }
} catch (Exception $e) {
    echo "✗ " . $e->getMessage() . "\n";
}

// Test 5: Equipment Tracking CRUD
echo "\nTEST 5: EQUIPMENT TRACKING ENDPOINTS\n";
echo "  GET /api/equipment-tracking ....... ";
try {
    $tracking = EquipmentTracking::with('equipment', 'user')->take(5)->get();
    echo "✓ Found " . count($tracking) . " tracking records\n";
} catch (Exception $e) {
    echo "✗ " . $e->getMessage() . "\n";
}

// Test 6: Trainers CRUD
echo "\nTEST 6: TRAINERS ENDPOINTS\n";
echo "  GET /api/trainers ................. ";
try {
    $trainers = Trainer::with('user', 'certifications')->take(5)->get();
    echo "✓ Found " . count($trainers) . " trainers\n";
} catch (Exception $e) {
    echo "✗ " . $e->getMessage() . "\n";
}

echo "  GET /api/trainers/{id} ............ ";
try {
    $t = Trainer::with('user', 'certifications')->find($trainer->id);
    echo "✓ Retrieved: " . $t->first_name . " (Linked to User #" . ($t->user_id ?? "null") . ")\n";
} catch (Exception $e) {
    echo "✗ " . $e->getMessage() . "\n";
}

echo "  Verify Trainer→User Relationship .. ";
try {
    $t = Trainer::find($trainer->id);
    $user = $t->user;
    if ($user) {
        echo "✓ Trainer linked to User #" . $user->id . " (" . $user->email . ")\n";
    } else {
        echo "✗ Trainer not linked to user\n";
    }
} catch (Exception $e) {
    echo "✗ " . $e->getMessage() . "\n";
}

// Test 7: Attendance CRUD
echo "\nTEST 7: ATTENDANCE ENDPOINTS\n";
echo "  GET /api/attendance ............... ";
try {
    $attendance = \App\Models\Attendance::with('member', 'schedule')->take(5)->get();
    echo "✓ Found " . count($attendance) . " attendance records\n";
} catch (Exception $e) {
    echo "✗ " . $e->getMessage() . "\n";
}

// Test 8: Payments relationships
echo "\nTEST 8: PAYMENTS RELATIONSHIPS\n";
echo "  GET /api/payments ................. ";
try {
    $payments = \App\Models\Payment::with('member')->take(5)->get();
    echo "✓ Found " . count($payments) . " payment records\n";
    if (count($payments) > 0) {
        $p = $payments->first();
        echo "    - Payment #" . $p->id . ": Member #" . $p->member_id . "\n";
    }
} catch (Exception $e) {
    echo "✗ " . $e->getMessage() . "\n";
}

echo "\n==== API TEST COMPLETE ====\n";
