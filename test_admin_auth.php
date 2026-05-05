<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

echo "=== Testing Admin Login ===\n\n";

try {
    // Test 1: Check admin user exists
    $admin = User::where('email', 'admin@gym.com')->first();
    echo "1. Admin user lookup: " . ($admin ? "✅ Found" : "❌ Not found") . "\n";
    
    if ($admin) {
        // Test 2: Check password hash
        $passwordValid = Hash::check('admin123', $admin->password);
        echo "2. Password verification: " . ($passwordValid ? "✅ Valid" : "❌ Invalid") . "\n";
        
        // Test 3: Try auth attempt
        echo "3. Auth::attempt() result: ";
        $authAttempt = Auth::attempt(['email' => 'admin@gym.com', 'password' => 'admin123']);
        echo ($authAttempt ? "✅ Success" : "❌ Failed") . "\n";
        
        // Test 4: Check if user should be active
        echo "4. User is_active status: " . ($admin->is_active ? "✅ Active" : "❌ Inactive") . "\n";
        
        // Test 5: Check auth middleware rules
        echo "\n5. Checking guard/middleware configuration:\n";
        echo "   - Default guard: " . config('auth.defaults.guard') . "\n";
        echo "   - User provider: " . config('auth.defaults.provider') . "\n";
        
        // Test 6: Check if there are any middleware issues
        echo "\n6. Checking auth routes API:\n";
        $routes = app('router')->getRoutes();
        $authRoutes = array_filter($routes->getRoutes(), function($route) {
            return strpos($route->uri, 'login') !== false || strpos($route->uri, 'auth') !== false;
        });
        echo "   - Found " . count($authRoutes) . " auth-related routes\n";
        foreach ($authRoutes as $route) {
            echo "     * " . $route->uri . " [" . implode(',', $route->methods) . "]\n";
        }
    }
    
    echo "\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
