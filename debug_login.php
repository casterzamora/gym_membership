<?php
error_log("Test Login Debug Started");

try {
    // Load Laravel
    $app = require __DIR__ . '/bootstrap/app.php';
    error_log("App loaded");
    
    // Get the auth controller
    $auth = app(\App\Http\Controllers\Api\AuthController::class);
    error_log("AuthController instantiated");
    
    // Check if admin user exists
    $user = \App\Models\User::where('email', 'admin@gym.com')->first();
    error_log("Admin lookup: " . ($user ? "Found user ID " . $user->id : "User not found"));
    
    if ($user) {
        error_log("User password field type: " . var_export($user->password, true));
        error_log("Can verify password: " . (\Illuminate\Support\Facades\Hash::check('password', $user->password) ? "Yes" : "No"));
    }
    
} catch (\Exception $e) {
    error_log("Error: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
}

error_log("Test Login Debug Ended");
