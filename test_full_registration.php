<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

try {
    echo "=== Testing Full Member Registration & Login Flow ===\n\n";
    
    $controller = new AuthController();
    
    // Test 1: Registration
    echo "TEST 1: Member Registration\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $testEmail = 'newmember' . time() . '@gym.com';
    $testPassword = 'TestPass123!';
    
    $registerRequest = Request::create('/api/register', 'POST', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => $testEmail,
        'password' => $testPassword,
        'password_confirmation' => $testPassword,
        'phone' => '9876543210',
        'date_of_birth' => null,
        'plan_id' => 1,
        'fitness_goal' => 'Build muscle',
        'health_notes' => null,
        'registration_type' => 'standard',
    ]);
    
    $registerResponse = $controller->register($registerRequest);
    $registerData = json_decode($registerResponse->getContent(), true);
    
    if ($registerData['success'] ?? false) {
        echo "✓ Registration successful\n";
        echo "  - User ID: " . ($registerData['data']['user']['id'] ?? 'N/A') . "\n";
        echo "  - Email: " . ($registerData['data']['user']['email'] ?? 'N/A') . "\n";
        echo "  - Token: " . (strlen($registerData['data']['token'] ?? '') > 0 ? 'Generated' : 'N/A') . "\n";
    } else {
        echo "❌ Registration failed: " . ($registerData['message'] ?? 'Unknown error') . "\n";
        if (isset($registerData['errors'])) {
            foreach ($registerData['errors'] as $field => $messages) {
                echo "   $field: " . implode(', ', $messages) . "\n";
            }
        }
    }
    
    // Test 2: Login
    echo "\n\nTEST 2: Member Login\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $loginRequest = Request::create('/api/login', 'POST', [
        'email' => $testEmail,
        'password' => $testPassword,
    ]);
    
    $loginResponse = $controller->login($loginRequest);
    $loginData = json_decode($loginResponse->getContent(), true);
    
    if ($loginData['success'] ?? false) {
        echo "✓ Login successful\n";
        echo "  - User ID: " . ($loginData['data']['user']['id'] ?? 'N/A') . "\n";
        echo "  - Email: " . ($loginData['data']['user']['email'] ?? 'N/A') . "\n";
        echo "  - Role: " . ($loginData['data']['user']['role'] ?? 'N/A') . "\n";
        echo "  - Token: " . (strlen($loginData['data']['token'] ?? '') > 0 ? 'Generated' : 'N/A') . "\n";
    } else {
        echo "❌ Login failed: " . ($loginData['message'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n✅ All tests completed\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
