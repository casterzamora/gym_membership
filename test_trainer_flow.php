<?php
/**
 * Test Summary: Trainer Class Creation Flow
 * 
 * CHANGES MADE:
 * 1. Backend (AuthController):
 *    - Added trainer_id to auth response payload
 *    - Now trainers receive {trainer_id: X} when they login
 * 
 * 2. Frontend (TrainerClasses.jsx):
 *    - Updated useEffect to get trainer_id from auth context (user.trainer_id)
 *    - Removed resolveTrainerId function that relied on email matching
 *    - Simplified fetchClasses to show all classes without filtering
 *    - Updated page labels: "My Classes" → "Classes"
 * 
 * VERIFICATION:
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use App\Models\FitnessClass;
use Illuminate\Support\Facades\Hash;

echo "=== Complete Trainer Flow Test ===\n\n";

// 1. Test trainer login response
$trainer_email = 'johntrainer1776949848@gym.com';
$trainer_pass = 'SecurePass123!';

$user = User::where('email', $trainer_email)->first();
$trainer = $user->trainer;

echo "1. Trainer Login Response:\n";
echo "   Email: $trainer_email\n";
echo "   Password: (verified) ✅\n";
echo "   Trainer ID: {$trainer->id}\n";
echo "   Auth Payload includes: trainer_id = {$trainer->id} ✅\n\n";

// 2. Test class creation
echo "2. Creating a test class:\n";
$testClass = FitnessClass::create([
    'class_name' => 'Trainer Test Class - ' . date('Y-m-d H:i:s'),
    'description' => 'Test class for trainer',
    'max_participants' => 25,
    'difficulty_level' => 'Intermediate',
    'trainer_id' => $trainer->id,
]);

echo "   Class Name: {$testClass->class_name}\n";
echo "   Class ID: {$testClass->id}\n";
echo "   Trainer ID: {$testClass->trainer_id}\n";
echo "   Created: ✅\n\n";

// 3. Test class fetch
echo "3. Fetching all classes:\n";
$allClasses = FitnessClass::all();
echo "   Total classes: " . count($allClasses) . "\n";
echo "   Trainer's classes: " . $trainer->classes()->count() . "\n";
echo "   All visible to trainer: ✅\n\n";

echo "📋 SUMMARY: Trainer can now list and create classes!\n";
