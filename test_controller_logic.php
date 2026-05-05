<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simulate what the controller does
$data = [
    'first_name' => 'ControllerTest',
    'last_name' => 'Trainer',
    'email' => 'controllertest' . time() . '@gym.com',
    'phone' => '555-9999',
    'specialization' => 'CrossFit',
    'hourly_rate' => 75,
];

echo "Step 1: Initial data\n";
echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

echo "Step 2: Creating User...\n";
try {
    $user = \App\Models\User::create([
        'name' => trim($data['first_name'] . ' ' . $data['last_name']),
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'email' => $data['email'],
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
        'phone' => $data['phone'] ?? null,
        'specialization' => $data['specialization'] ?? null,
        'hourly_rate' => $data['hourly_rate'] ?? 0,
        'role' => 'trainer',
        'is_active' => true,
    ]);
    echo "✅ User created. ID: " . $user->id . "\n";
    $data['user_id'] = $user->id;
} catch (\Exception $e) {
    echo "❌ User creation failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nStep 3: Data after setting user_id\n";
echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

echo "Step 4: Creating Trainer with Modified Data...\n";
try {
    $trainer = \App\Models\Trainer::create($data);
    echo "✅ Trainer created. ID: " . $trainer->id . "\n";
    echo "   User ID: " . $trainer->user_id . "\n";
    echo "   User Email: " . $trainer->user->email . "\n";
} catch (\Exception $e) {
    echo "❌ Trainer creation failed: " . $e->getMessage() . "\n";
    exit(1);
}
