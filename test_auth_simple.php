<?php
// Simple test of the login flow
require __DIR__ . '/vendor/autoload.php';

try {
    $app = require __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    
    echo "1. App loaded\n";
    
    // Test User model
    $user = \App\Models\User::where('email', 'admin@gym.com')->first();
    if ($user) {
        echo "2. User found: " . $user->name . "\n";
        echo "3. Testing password: " . (\Illuminate\Support\Facades\Hash::check('password', $user->password) ? "PASS" : "FAIL") . "\n";
        echo "4. Creating token...\n";
        $token = $user->createToken('api-token')->plainTextToken;
        echo "5. Token created: " . substr($token, 0, 20) . "...\n";
        echo "6. Login test SUCCESSFUL\n";
    } else {
        echo "2. User NOT found\n";
        echo "Creating admin...\n";
        $newUser = \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@gym.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin',
        ]);
        echo "3. Admin created with ID: " . $newUser->id . "\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
