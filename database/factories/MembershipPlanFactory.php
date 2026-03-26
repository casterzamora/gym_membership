<?php

namespace Database\Factories;

use App\Models\MembershipPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class MembershipPlanFactory extends Factory
{
    protected $model = MembershipPlan::class;

    public function definition(): array
    {
        static $counter = 0;
        $counter++;
        
        $plans = [
            ['name' => 'Bronze Monthly', 'price' => 49.99, 'months' => 1],
            ['name' => 'Silver Monthly', 'price' => 79.99, 'months' => 1],
            ['name' => 'Gold Annual', 'price' => 799.99, 'months' => 12],
            ['name' => 'Platinum Annual', 'price' => 1299.99, 'months' => 12],
        ];

        // Use the array's index to cycle through plans, making each unique
        $plan = $plans[($counter - 1) % count($plans)];

        return [
            'plan_name' => $plan['name'] . ($counter > count($plans) ? ' ' . $counter : ''),
            'price' => $plan['price'],
            'duration_months' => $plan['months'],
            'description' => $this->faker->sentence(),
        ];
    }
}
