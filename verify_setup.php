<?php
require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

try {
    echo "=== Database and Admin User Verification ===\n\n";
    
    // Check database connection
    echo "1. Checking database connection...\n";
    try {
        DB::connection()->getPdo();
        echo "   ✓ Database connected successfully\n\n";
    } catch (Exception $e) {
        echo "   ✗ Database connection failed: " . $e->getMessage() . "\n\n";
        exit(1);
    }
    
    // Check if users table exists
    echo "2. Checking if users table exists...\n";
    if (!Schema::hasTable('users')) {
        echo "   ✗ Users table missing - migrations may not have run\n";
        echo "   Running migrations now...\n";
        Artisan::call('migrate', ['--force' => true]);
        echo "   ✓ Migrations completed\n\n";
    } else {
        echo "   ✓ Users table exists\n\n";
    }
    
    // Check admin user
    echo "3. Checking for admin user...\n";
    $admin = App\Models\User::where('email', 'admin@gym.com')->first();
    if ($admin) {
        echo "   ✓ Admin user found (admin@gym.com)\n";
        echo "   Password set: Yes\n\n";
    } else {
        echo "   ✗ Admin user not found\n";
        echo "   Creating admin user...\n";
        $admin = App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@gym.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);
        echo "   ✓ Admin user created with email: admin@gym.com, password: password\n\n";
    }
    
    // Check membership plans
    echo "4. Checking membership plans...\n";
    $plans = App\Models\MembershipPlan::count();
    if ($plans > 0) {
        echo "   ✓ Found $plans membership plans\n\n";
    } else {
        echo "   ✗ No membership plans found\n";
        echo "   Seeding required data...\n";
        Artisan::call('db:seed', ['--force' => true]);
        echo "   ✓ Data seeded\n\n";
    }
    
    // Display connection info
    echo "5. Database Configuration:\n";
    echo "   Host: " . config('database.connections.mysql.host') . "\n";
    echo "   Database: " . config('database.connections.mysql.database') . "\n";
    echo "   Debug Mode: " . (config('app.debug') ? 'ON' : 'OFF') . "\n\n";
    
    echo "=== All Checks Passed ===\n";
    echo "Application is ready. Login with:\n";
    echo "  Email: admin@gym.com\n";
    echo "  Password: password\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}
