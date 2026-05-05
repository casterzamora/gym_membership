<?php
echo "=== GYM MEMBERSHIP - DATABASE SETUP ===\n\n";

try {
    // Load the application
    $app = require __DIR__ . '/bootstrap/app.php';
    
    echo "✓ Application loaded\n";
    
    // Get database and kernel
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    
    // Run all pending migrations
    echo "\n--- Running Migrations ---\n";
    $kernel->call('migrate', ['--force' => true]);
    echo "✓ Migrations completed\n";
    
    // Check if admin user exists
    echo "\n--- Checking Admin User ---\n";
    $adminUser = \App\Models\User::where('email', 'admin@gym.com')->first();
    
    if ($adminUser) {
        echo "✓ Admin user exists (ID: {$adminUser->id})\n";
    } else {
        echo "✗ Admin user not found, creating...\n";
        $adminUser = \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@gym.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        echo "✓ Created admin user (ID: {$adminUser->id})\n";
    }
    
    // Check if membership plans exist
    echo "\n--- Checking Membership Plans ---\n";
    $planCount = \App\Models\MembershipPlan::count();
    if ($planCount > 0) {
        echo "✓ Found $planCount membership plans\n";
    } else {
        echo "✗ No plans found, running seeders...\n";
        $kernel->call('db:seed');
        echo "✓ Seeders completed\n";
    }
    
    echo "\n=== SETUP COMPLETE ===\n";
    echo "You can now login with:\n";
    echo "  Email: admin@gym.com\n";
    echo "  Password: password\n";
    
} catch (\Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
