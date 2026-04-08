<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

use App\Models\MembershipPlan;

$plan = MembershipPlan::create([
    'plan_name' => 'Basic Plan',
    'description' => 'Perfect for beginners',
    'price' => 29.99,
    'duration_days' => 30,
    'max_classes_per_week' => 5,
    'max_personal_training_sessions' => 2,
    'benefits' => json_encode(['Gym Access', 'Basic Equipment', 'Email Support']),
    'status' => 'active'
]);

echo "✓ Created membership plan: " . $plan->plan_name . " (ID: " . $plan->id . ")\n";
