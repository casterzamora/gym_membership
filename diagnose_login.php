<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use App\Models\Member;

try {
    echo "=== Diagnosing Login Issue ===\n\n";
    
    // Find all members
    $users = User::where('role', 'member')->with('member')->get();
    
    echo "Total member users: " . $users->count() . "\n\n";
    
    foreach ($users as $user) {
        echo "User #{$user->id}: {$user->email}\n";
        echo "  - Name: {$user->first_name} {$user->last_name}\n";
        echo "  - is_active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
        
        if ($user->member) {
            echo "  ✓ Member profile exists (ID: {$user->member->id})\n";
            echo "    - Status: {$user->member->membership_status}\n";
        } else {
            echo "  ❌ NO MEMBER PROFILE FOUND\n";
            
            // Check if member exists for this user_id
            $memberFromDb = Member::where('user_id', $user->id)->first();
            if ($memberFromDb) {
                echo "    - BUT member exists in DB (ID: {$memberFromDb->id})\n";
                echo "    - Relationship is broken!\n";
            }
        }
        echo "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
