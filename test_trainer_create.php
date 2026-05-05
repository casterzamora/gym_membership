<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test trainer creation
$data = [
    'first_name' => 'Test',
    'last_name' => 'Trainer',
    'email' => 'testtrainer' . time() . '@gym.com',
    'phone' => '555-1234',
    'specialization' => 'Fitness',
    'hourly_rate' => 50,
];

echo "Testing trainer creation with data:\n";
echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

try {
    $trainer = \App\Models\Trainer::create($data);
    echo "SUCCESS: Trainer created with ID: " . $trainer->id . "\n";
    echo "Trainer User ID: " . $trainer->user_id . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
}
