<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

try {
    echo "Testing login endpoint directly...\n\n";
    
    // Simulate a login request
    $controller = new AuthController();
    $controller->useTrait(ApiResponse::class);
    
    // Create a mock request
    $request = Request::create('/api/login', 'POST', [
        'email' => 'admin@gym.com',
        'password' => 'admin123',
    ]);
    $request->headers->set('Content-Type', 'application/json');
    
    // Call the login method
    $response = $controller->login($request);
    
    echo "Response:\n";
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nFull trace:\n";
    echo $e->getTraceAsString() . "\n";
}
