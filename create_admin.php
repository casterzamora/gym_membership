<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

try {
    $user = User::create([
        'name' => 'Admin User',
        'email' => 'admin@gym.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);

    echo "✅ Admin user created successfully!\n";
    echo "📧 Email: admin@gym.com\n";
    echo "🔑 Password: password\n";
    echo "👤 Role: admin\n";
} catch (\Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        echo "⚠️  Admin user already exists at admin@gym.com\n";
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}
