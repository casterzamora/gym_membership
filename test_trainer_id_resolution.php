<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use App\Models\Trainer;

echo "=== Testing Trainer ID Resolution ===\n\n";

// Test with the trainer we created
$trainerEmail = 'johntrainer1776949848@gym.com';

echo "1. Looking for trainer: $trainerEmail\n";
$user = User::where('email', $trainerEmail)->first();
echo "   - User found: " . ($user ? "✅ Yes (ID: {$user->id})" : "❌ No") . "\n";

if ($user) {
    echo "   - User role: {$user->role}\n";
    echo "   - User name: {$user->first_name} {$user->last_name}\n";
}

echo "\n2. Looking for trainer record:\n";
$trainer = Trainer::where('email', $trainerEmail)->first();
echo "   - Trainer found: " . ($trainer ? "✅ Yes (ID: {$trainer->id})" : "❌ No") . "\n";

if ($trainer) {
    echo "   - Trainer name: {$trainer->first_name} {$trainer->last_name}\n";
    echo "   - Trainer user_id: {$trainer->user_id}\n";
}

echo "\n3. All trainers in system:\n";
$allTrainers = Trainer::all();
echo "   - Total trainers: " . count($allTrainers) . "\n";
foreach ($allTrainers as $t) {
    echo "   - {$t->first_name} {$t->last_name} ({$t->email})\n";
}
