<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

// Run migration status
$exitCode = $kernel->call('migrate:status');

echo "\n===== Checking if admin user exists =====\n";

// Check if admin user exists
$user = \App\Models\User::where('email', 'admin@gym.com')->first();
if ($user) {
    echo "✓ Admin user found:\n";
    echo "  ID: " . $user->id . "\n";
    echo "  Name: " . $user->name . "\n";
    echo "  Email: " . $user->email . "\n";
    echo "  Role: " . ($user->role ?? 'N/A') . "\n";
} else {
    echo "✗ Admin user NOT found\n";
    echo "Creating admin user...\n";
    
    $admin = \App\Models\User::create([
        'name' => 'Admin User',
        'email' => 'admin@gym.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);
    
    echo "✓ Admin user created with ID: " . $admin->id . "\n";
}

echo "\n===== Running seeders =====\n";
$kernel->call('db:seed');
