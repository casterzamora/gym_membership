<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    echo "=== Checking Admin Account ===\n\n";
    
    // Get admin user
    $admin = User::where('role', 'admin')->first();
    
    if (!$admin) {
        echo "❌ Admin user not found\n";
        exit(1);
    }
    
    echo "Admin Account Details:\n";
    echo "  - ID: {$admin->id}\n";
    echo "  - Email: {$admin->email}\n";
    echo "  - Name: {$admin->first_name} {$admin->last_name}\n";
    echo "  - Active: " . ($admin->is_active ? 'Yes' : 'No') . "\n";
    echo "  - Role: {$admin->role}\n";
    echo "  - Password hash: " . substr($admin->password, 0, 20) . "...\n\n";
    
    // Test login with known password
    echo "Testing login with 'admin123'...\n";
    $passwordMatches = Hash::check('admin123', $admin->password);
    echo "  - Password matches: " . ($passwordMatches ? 'YES' : 'NO') . "\n";
    
    if (!$passwordMatches) {
        echo "\n⚠️  Password doesn't match 'admin123'\n";
        echo "Setting admin password to 'admin123'...\n";
        
        $admin->password = Hash::make('admin123');
        $admin->save();
        
        echo "✅ Admin password reset to 'admin123'\n";
    } else {
        echo "\n✅ Admin password is correct\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
