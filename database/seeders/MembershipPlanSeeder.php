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
        foreach ([
            ['plan_name' => 'Bronze', 'price' => 750, 'duration_months' => 1, 'max_classes_per_week' => 4, 'description' => 'Perfect for beginners'],
            ['plan_name' => 'Silver', 'price' => 1000, 'duration_months' => 2, 'max_classes_per_week' => 8, 'description' => 'For regular gym-goers'],
            ['plan_name' => 'Gold', 'price' => 1500, 'duration_months' => 3, 'max_classes_per_week' => 999, 'description' => 'Premium membership'],
        ] as $plan) {
            MembershipPlan::updateOrCreate(
                ['plan_name' => $plan['plan_name']],
                $plan
            );
        }
    }
}
