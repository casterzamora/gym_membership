<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use App\Models\Member;

try {
    echo "=== Member Account Status ===\n\n";
    
    // Get all member users
    $users = User::where('role', 'member')
        ->select('id', 'email', 'first_name', 'last_name', 'is_active')
        ->with('member:id,user_id,membership_status')
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    
    echo "Recent 10 member accounts:\n\n";
    
    foreach ($users as $user) {
        echo "Email: {$user->email}\n";
        echo "  - ID: {$user->id}\n";
        echo "  - Name: {$user->first_name} {$user->last_name}\n";
        echo "  - Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
        
        if ($user->member) {
            echo "  - Member Status: {$user->member->membership_status}\n";
            echo "  ✓ Can login\n";
        } else {
            echo "  ❌ NO MEMBER PROFILE\n";
        }
        echo "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
