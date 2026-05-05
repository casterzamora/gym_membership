<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Member;
use App\Models\MembershipPlan;
use Illuminate\Support\Facades\Hash;

// Get existing plans
$plans = MembershipPlan::limit(3)->get();
if ($plans->count() === 0) {
    echo "No membership plans found. Please seed the database first.\n";
    exit(1);
}

// Create test members
$members_data = [
    [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'username' => 'johndoe',
        'password_hash' => Hash::make('password123'),
        'phone' => '09123456789',
        'date_of_birth' => \Carbon\Carbon::now()->subYears(25)->toDateString(),
        'plan_id' => $plans[0]->id,
        'fitness_goal' => 'Weight Loss',
        'health_notes' => 'No injuries',
        'registration_type' => 'online',
        'membership_status' => 'active',
        'membership_start' => \Carbon\Carbon::now()->toDateString(),
        'membership_end' => \Carbon\Carbon::now()->addMonths($plans[0]->duration_months)->toDateString(),
    ],
    [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane.smith@example.com',
        'username' => 'janesmith',
        'password_hash' => Hash::make('password123'),
        'phone' => '09234567890',
        'date_of_birth' => \Carbon\Carbon::now()->subYears(30)->toDateString(),
        'plan_id' => $plans[1]->id,
        'fitness_goal' => 'Build Muscle',
        'health_notes' => 'Regular gym member',
        'registration_type' => 'walk-in',
        'membership_status' => 'active',
        'membership_start' => \Carbon\Carbon::now()->toDateString(),
        'membership_end' => \Carbon\Carbon::now()->addMonths($plans[1]->duration_months)->toDateString(),
    ],
    [
        'first_name' => 'Mike',
        'last_name' => 'Johnson',
        'email' => 'mike.johnson@example.com',
        'username' => 'mikejohnson',
        'password_hash' => Hash::make('password123'),
        'phone' => '09345678901',
        'date_of_birth' => \Carbon\Carbon::now()->subYears(35)->toDateString(),
        'plan_id' => $plans[0]->id,
        'fitness_goal' => 'Flexibility',
        'health_notes' => 'Recovering from injury',
        'registration_type' => 'referral',
        'membership_status' => 'active',
        'membership_start' => \Carbon\Carbon::now()->toDateString(),
        'membership_end' => \Carbon\Carbon::now()->addMonths($plans[0]->duration_months)->toDateString(),
    ],
];

foreach ($members_data as $data) {
    // Check if member already exists
    if (!Member::where('email', $data['email'])->exists()) {
        $member = Member::create($data);
        echo "✓ Created member: {$member->first_name} {$member->last_name} (ID: {$member->id})\n";
    } else {
        echo "~ Member already exists: {$data['email']}\n";
    }
}

echo "\n✓ Members setup complete!\n";
echo "\nAvailable members for upgrade testing:\n";
Member::select('id', 'first_name', 'last_name', 'plan_id', 'membership_status', 'membership_end')
    ->get()
    ->each(function ($m) use ($plans) {
        $plan = $plans->find($m->plan_id);
        echo "ID: {$m->id}, Name: {$m->first_name} {$m->last_name}, Plan: {$plan->plan_name}, Status: {$m->membership_status}\n";
    });
