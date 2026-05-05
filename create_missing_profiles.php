<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use App\Models\Member;
use App\Models\MembershipPlan;

try {
    echo "=== Creating Missing Member Profiles ===\n\n";
    
    // Get default plan
    $defaultPlan = MembershipPlan::first();
    if (!$defaultPlan) {
        echo "❌ No membership plans found. Create one first.\n";
        exit(1);
    }
    
    // Find users without member profiles
    $usersWithoutProfiles = User::where('role', 'member')
        ->whereDoesntHave('member')
        ->get();
    
    echo "Found " . $usersWithoutProfiles->count() . " users without member profiles\n\n";
    
    $created = 0;
    foreach ($usersWithoutProfiles as $user) {
        try {
            Member::create([
                'user_id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'date_of_birth' => null,
                'plan_id' => $defaultPlan->id,
                'fitness_goal' => null,
                'health_notes' => null,
                'registration_type' => 'standard',
                'membership_start' => now()->toDateString(),
                'membership_end' => now()->addMonths(3)->toDateString(),
                'membership_status' => 'active',
            ]);
            echo "✓ Created member profile for {$user->email}\n";
            $created++;
        } catch (\Exception $e) {
            echo "✗ Failed to create profile for {$user->email}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✅ Created $created member profiles\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
