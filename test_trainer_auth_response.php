<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== Testing Trainer Auth Response ===\n\n";

$trainerEmail = 'johntrainer1776949848@gym.com';
$trainerPassword = 'SecurePass123!';

// Test login
$user = User::where('email', $trainerEmail)->first();

if (!$user) {
    echo "❌ Trainer user not found\n";
    exit(1);
}

// Check password
if (!Hash::check($trainerPassword, $user->password)) {
    echo "❌ Password doesn't match\n";
    exit(1);
}

echo "✅ Trainer found and password matches\n";
echo "   - User ID: {$user->id}\n";
echo "   - Role: {$user->role}\n";

// Check trainer relationship
$trainer = $user->trainer;
if ($trainer) {
    echo "✅ Trainer profile found\n";
    echo "   - Trainer ID: {$trainer->id}\n";
    echo "   - Name: {$trainer->first_name} {$trainer->last_name}\n";
    
    // Simulate auth response
    echo "\n📝 Auth Response Payload:\n";
    $payload = [
        'id' => $user->id,
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'email' => $user->email,
        'phone' => $user->phone,
        'role' => $user->role,
        'type' => $user->role,
        'trainer_id' => $trainer->id // This is what we added
    ];
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} else {
    echo "❌ No trainer profile found for this user\n";
}
