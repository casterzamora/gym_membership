<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    echo "Testing login flow...\n";
    
    // Get admin user
    $admin = User::where('role', 'admin')->first();
    if (!$admin) {
        echo "❌ Admin user not found\n";
        exit(1);
    }
    
    echo "✓ Admin user found: {$admin->email}\n";
    echo "  ID: {$admin->id}\n";
    echo "  Name: {$admin->first_name} {$admin->last_name}\n";
    echo "  Is Active: " . ($admin->is_active ? 'Yes' : 'No') . "\n";
    echo "  Role: {$admin->role}\n";
    
    // Test password verification
    $testPassword = 'admin123';
    $isPasswordValid = Hash::check($testPassword, $admin->password);
    echo "\n✓ Testing password 'admin123': " . ($isPasswordValid ? 'VALID' : 'INVALID') . "\n";
    
    // Try to create a token
    echo "\nTrying to create API token...\n";
    $token = $admin->createToken('api-token')->plainTextToken;
    echo "✓ Token created successfully\n";
    echo "  Token: " . substr($token, 0, 20) . "...\n";
    
    // Simulate the buildAuthUserPayload logic
    echo "\nBuilding auth payload...\n";
    $payload = [
        'id' => $admin->id,
        'first_name' => $admin->first_name,
        'last_name' => $admin->last_name,
        'email' => $admin->email,
        'phone' => $admin->phone,
        'role' => $admin->role,
        'type' => $admin->role,
    ];
    echo "✓ Payload created: " . json_encode($payload) . "\n";
    
    echo "\n✅ All tests passed - login should work!\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nFull trace:\n";
    echo $e->getTraceAsString() . "\n";
}
