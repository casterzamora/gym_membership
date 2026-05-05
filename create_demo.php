<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\MembershipPlan;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    $plan = MembershipPlan::first();

    if (!$plan) {
        throw new RuntimeException('No membership plans found. Seed plans first.');
    }

    $user = User::firstOrCreate(
        ['email' => 'demo@gym.com'],
        [
            'name' => 'Demo User',
            'password' => Hash::make('password'),
            'role' => 'member',
            'is_active' => true,
            'email_verified_at' => now(),
        ]
    );

    Member::unguarded(function () use ($user, $plan) {
        Member::updateOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => 'Demo',
                'last_name' => 'User',
                'email' => 'demo@gym.com',
                'username' => 'demo',
                'password_hash' => Hash::make('password'),
                'phone' => '555-0000',
                'date_of_birth' => now()->subYears(30),
                'plan_id' => $plan->id,
                'fitness_goal' => 'General Fitness',
                'health_notes' => 'No restrictions',
                'registration_type' => 'standard',
                'membership_start' => now()->toDateString(),
                'membership_end' => now()->addMonths(3)->toDateString(),
                'membership_status' => 'active',
            ]
        );
    });

    echo "✅ Demo login created successfully!\n";
    echo "📧 Email: demo@gym.com\n";
    echo "🔑 Password: password\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}