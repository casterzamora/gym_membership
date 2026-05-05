<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "==== FIXING ADMIN PROFILE ====\n\n";

$admin = \App\Models\User::where('role', 'admin')->first();
if ($admin) {
    echo "Current admin:\n";
    echo "  ID: " . $admin->id . "\n";
    echo "  Email: " . $admin->email . "\n";
    echo "  Name: " . $admin->name . "\n";
    echo "  First Name: " . ($admin->first_name ?? 'NULL') . "\n";
    echo "  Last Name: " . ($admin->last_name ?? 'NULL') . "\n\n";
    
    // Update with proper first/last names
    $admin->update([
        'first_name' => 'System',
        'last_name' => 'Administrator',
    ]);
    
    echo "Updated admin:\n";
    echo "  First Name: " . $admin->first_name . "\n";
    echo "  Last Name: " . $admin->last_name . "\n";
    echo "\n✓ Admin profile updated successfully\n";
} else {
    echo "✗ Admin user not found\n";
}

// Also test that login still works
echo "\n==== TESTING LOGIN ====\n";
$admin = \App\Models\User::where('role', 'admin')->first();
if ($admin) {
    $token = $admin->createToken('api-token')->plainTextToken;
    echo "✓ Token created: " . substr($token, 0, 20) . "...\n";
    
    // Simulate the buildAuthUserPayload function
    $payload = [
        'id' => $admin->id,
        'first_name' => $admin->first_name,
        'last_name' => $admin->last_name,
        'email' => $admin->email,
        'phone' => $admin->phone,
        'role' => $admin->role,
        'type' => $admin->role,
    ];
    
    echo "\nLogin response payload:\n";
    echo json_encode($payload, JSON_PRETTY_PRINT);
    echo "\n\n✓ Login would return proper first/last names\n";
}
