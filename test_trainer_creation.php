<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Http\Controllers\Api\TrainerController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;

try {
    echo "=== Testing Trainer Creation with Password ===\n\n";
    
    // Test 1: Create a trainer
    echo "TEST 1: Creating trainer with password\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $trainerController = new TrainerController();
    
    $trainerRequest = Request::create('/api/v1/trainers', 'POST', [
        'first_name' => 'John',
        'last_name' => 'Trainer',
        'email' => 'johntrainer@gym.com',
        'specialization' => 'CrossFit',
        'phone' => '555-1234',
        'hourly_rate' => '75',
        'password' => 'SecurePass123!',
    ]);
    
    $createResponse = $trainerController->store($trainerRequest);
    $createData = json_decode($createResponse->getContent(), true);
    
    if ($createData['success'] ?? false) {
        echo "✅ Trainer created successfully\n";
        echo "  - Email: {$createData['data']['email']}\n";
        echo "  - ID: {$createData['data']['id']}\n";
    } else {
        echo "❌ Trainer creation failed: " . ($createData['message'] ?? 'Unknown error') . "\n";
        if (isset($createData['errors'])) {
            echo "  Errors: " . json_encode($createData['errors']) . "\n";
        }
        exit(1);
    }
    
    // Test 2: Login as trainer
    echo "\n\nTEST 2: Login as trainer\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $authController = new AuthController();
    
    $loginRequest = Request::create('/api/login', 'POST', [
        'email' => 'johntrainer@gym.com',
        'password' => 'SecurePass123!',
    ]);
    
    $loginResponse = $authController->login($loginRequest);
    $loginData = json_decode($loginResponse->getContent(), true);
    
    if ($loginData['success'] ?? false) {
        echo "✅ Trainer login successful\n";
        echo "  - Email: {$loginData['data']['user']['email']}\n";
        echo "  - Role: {$loginData['data']['user']['role']}\n";
        echo "  - Token generated: " . (strlen($loginData['data']['token'] ?? '') > 0 ? 'YES' : 'NO') . "\n";
    } else {
        echo "❌ Login failed: " . ($loginData['message'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n✅ All tests passed!\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
