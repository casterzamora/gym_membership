<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use App\Models\Member;

try {
    echo "=== Complete Member Login Verification ===\n\n";
    
    // Get a sample of all member accounts
    $members = User::where('role', 'member')
        ->select('id', 'email', 'first_name', 'is_active')
        ->with('member:user_id,membership_status')
        ->get();
    
    $working = 0;
    $broken = 0;
    
    echo "Checking " . $members->count() . " member accounts:\n\n";
    
    foreach ($members as $user) {
        $status = '';
        
        // Check all conditions
        if (!$user->is_active) {
            $status = '❌ User inactive';
            $broken++;
        } elseif (!$user->member) {
            $status = '❌ No member profile';
            $broken++;
        } elseif ($user->member->membership_status !== 'active') {
            $status = '❌ Membership not active (status: ' . $user->member->membership_status . ')';
            $broken++;
        } else {
            $status = '✓ Can login';
            $working++;
        }
        
        echo "[{$user->id}] {$user->email}: $status\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Summary:\n";
    echo "  Working: $working\n";
    echo "  Broken: $broken\n";
    echo "  Total: " . $members->count() . "\n";
    
    if ($broken > 0) {
        echo "\n❌ Found $broken accounts with issues\n";
        
        // Show broken accounts
        echo "\nBroken accounts:\n";
        foreach ($members as $user) {
            if (!$user->is_active || !$user->member || $user->member->membership_status !== 'active') {
                echo "  - {$user->email}\n";
            }
        }
    } else {
        echo "\n✅ All member accounts are working!\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
