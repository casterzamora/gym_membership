<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use App\Models\Member;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

try {
    echo "=== Testing casterzamora1@gmail.com Login ===\n\n";
    
    $email = 'casterzamora1@gmail.com';
    $password = 'password';
    
    // Check user account
    echo "Step 1: Check user account\n";
    $user = User::where('email', $email)->first();
    
    if (!$user) {
        echo "  ❌ User not found in database\n";
        exit(1);
    }
    
    echo "  ✓ User found (ID: {$user->id})\n";
    echo "    - Name: {$user->first_name} {$user->last_name}\n";
    echo "    - Role: {$user->role}\n";
    echo "    - Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
    
    // Check password
    echo "\nStep 2: Check password\n";
    $passwordMatches = Hash::check($password, $user->password);
    echo "  - Password matches: " . ($passwordMatches ? 'YES' : 'NO') . "\n";
    
    if (!$passwordMatches) {
        echo "  ℹ If password doesn't match, you may have set a different password\n";
        echo "    The account exists but the password is incorrect.\n";
    }
    
    // Check member profile
    echo "\nStep 3: Check member profile\n";
    $member = Member::where('user_id', $user->id)->first();
    
    if (!$member) {
        echo "  ❌ Member profile not found!\n";
        echo "  Creating member profile...\n";
        
        // Create the missing profile
        $member = Member::create([
            'user_id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
            'date_of_birth' => null,
            'plan_id' => 1,
            'fitness_goal' => null,
            'health_notes' => null,
            'registration_type' => 'standard',
            'membership_start' => now()->toDateString(),
            'membership_end' => now()->addMonths(3)->toDateString(),
            'membership_status' => 'active',
        ]);
        
        echo "  ✓ Member profile created!\n";
    } else {
        echo "  ✓ Member profile found (ID: {$member->id})\n";
        echo "    - Status: {$member->membership_status}\n";
    }
    
    // Try login via controller
    echo "\nStep 4: Test login via API\n";
    if ($passwordMatches) {
        $controller = new AuthController();
        $loginRequest = Request::create('/api/login', 'POST', [
            'email' => $email,
            'password' => $password,
        ]);
        
        $response = $controller->login($loginRequest);
        $data = json_decode($response->getContent(), true);
        
        if ($data['success'] ?? false) {
            echo "  ✓ Login successful!\n";
        } else {
            echo "  ❌ Login failed: " . ($data['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "  ⚠️  Skipping login test (password doesn't match)\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
