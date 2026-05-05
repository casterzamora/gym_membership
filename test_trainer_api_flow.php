<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "==== ADMIN LOGIN & TRAINER CREATE TEST ====\n\n";

// Fetch admin user
$admin = User::where('role', 'admin')->first();
if (!$admin) {
    echo "✗ Admin user not found\n";
    exit(1);
}

echo "✓ Admin found: " . $admin->email . "\n";
echo "✓ Creating token...\n";
$token = $admin->createToken('api-token')->plainTextToken;
echo "✓ Token: " . substr($token, 0, 20) . "...\n\n";

// Now test creating trainer
echo "TEST: Creating trainer via API simulation\n";
$trainerData = [
    'first_name' => 'APITest',
    'last_name' => 'Trainer',
    'email' => 'apitest' . time() . '@gym.test',
    'phone' => '555-API1',
    'specialization' => 'API Testing',
    'hourly_rate' => 99.99,
];

echo "Trainer data to send:\n";
foreach ($trainerData as $k => $v) {
    echo "  $k: $v\n";
}

// Simulate the request
$request = app(\Illuminate\Http\Request::class);
$request->initialize([], $trainerData, [], [], [], [
    'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
    'CONTENT_TYPE' => 'application/json',
    'HTTP_ACCEPT' => 'application/json',
]);
$request->setMethod('POST');

echo "\n✓ Request prepared\n";
echo "✓ Token in auth header: " . (bool)$request->bearerToken() . "\n";
echo "✓ User authenticated: " . (bool)auth()->check() . "\n";

// Try to validate the request like the controller would
$validator = \Illuminate\Support\Facades\Validator::make($trainerData, [
    'first_name' => 'sometimes|string|max:255',
    'last_name' => 'sometimes|string|max:255',
    'phone' => 'sometimes|string|max:20',
    'specialization' => 'sometimes|string|max:255',
    'hourly_rate' => 'sometimes|numeric|min:0',
    'email' => 'required|email|unique:users',
]);

if ($validator->passes()) {
    echo "✓ Validation passed\n\n";
    
    // Create the trainer
    try {
        $user = User::create([
            'name' => trim($trainerData['first_name'] . ' ' . $trainerData['last_name']),
            'first_name' => $trainerData['first_name'],
            'last_name' => $trainerData['last_name'],
            'email' => $trainerData['email'],
            'password' => Hash::make('temp-pass'),
            'phone' => $trainerData['phone'],
            'specialization' => $trainerData['specialization'],
            'hourly_rate' => $trainerData['hourly_rate'],
            'role' => 'trainer',
            'is_active' => true,
        ]);
        echo "✓ User created: #" . $user->id . "\n";
        
        $trainer = \App\Models\Trainer::create([
            'user_id' => $user->id,
            'first_name' => $trainerData['first_name'],
            'last_name' => $trainerData['last_name'],
            'specialization' => $trainerData['specialization'],
            'phone' => $trainerData['phone'],
            'hourly_rate' => $trainerData['hourly_rate'],
        ]);
        echo "✓ Trainer created: #" . $trainer->id . "\n";
        
        // Load relationships
        $trainer->load('user', 'certifications', 'classes');
        echo "✓ Trainer loaded with relationships\n\n";
        
        // Show response format
        echo "API Response would be:\n";
        $response = [
            'success' => true,
            'message' => 'Trainer created successfully',
            'data' => $trainer,
        ];
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        
    } catch (\Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
        echo "Stack: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
} else {
    echo "✗ Validation failed:\n";
    foreach ($validator->errors()->all() as $error) {
        echo "  - $error\n";
    }
}

echo "\n==== TEST COMPLETE ====\n";
