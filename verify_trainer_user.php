<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get the last trainer created
$trainer = \App\Models\Trainer::latest('id')->first();

if ($trainer) {
    echo "✅ Trainer Created:\n";
    echo "  ID: " . $trainer->id . "\n";
    echo "  Name: " . $trainer->first_name . " " . $trainer->last_name . "\n";
    echo "  Email: " . $trainer->email . "\n";
    echo "  User ID: " . $trainer->user_id . "\n";
    
    if ($trainer->user) {
        echo "\n✅ Linked User Account:\n";
        echo "  User ID: " . $trainer->user->id . "\n";
        echo "  User Email: " . $trainer->user->email . "\n";
        echo "  User Role: " . $trainer->user->role . "\n";
        echo "  Password Hash: " . substr($trainer->user->password, 0, 30) . "...\n";
        
        // Test password
        $testPassword = 'password';
        $matches = \Illuminate\Support\Facades\Hash::check($testPassword, $trainer->user->password);
        if ($matches) {
            echo "  ✅ Password 'password' matches the hash!\n";
        } else {
            echo "  ❌ Password test failed\n";
        }
    } else {
        echo "\n❌ ERROR: No linked user found!\n";
    }
} else {
    echo "❌ No trainers found\n";
}
