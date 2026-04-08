<?php

namespace Database\Seeders;

use App\Models\MembershipPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MembershipPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MembershipPlan::create([
            'plan_name' => 'Basic Plan',
            'description' => 'Perfect for beginners',
            'price' => 29.99,
            'duration_days' => 30,
            'max_classes_per_week' => 5,
            'max_personal_training_sessions' => 2,
            'benefits' => json_encode(['Gym Access', 'Basic Equipment', 'Email Support']),
            'status' => 'active'
        ]);
    }
}
