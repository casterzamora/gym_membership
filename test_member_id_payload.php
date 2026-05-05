<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;

try {
    echo "=== Testing Auth Payload with member_id ===\n\n";
    
    $controller = new AuthController();
    
    // Login as an existing member
    $loginRequest = Request::create('/api/login', 'POST', [
        'email' => 'casterzamora1@gmail.com',
        'password' => 'password',
    ]);
    
    $response = $controller->login($loginRequest);
    $data = json_decode($response->getContent(), true);
    
    if ($data['success'] ?? false) {
        echo "✓ Login successful\n\n";
        echo "Auth Payload:\n";
        echo json_encode($data['data']['user'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
        
        if (isset($data['data']['user']['member_id'])) {
            echo "✅ member_id IS included in auth payload: " . $data['data']['user']['member_id'] . "\n";
        } else {
            echo "❌ member_id NOT included in auth payload\n";
        }
    } else {
        echo "❌ Login failed: " . ($data['message'] ?? 'Unknown error') . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
