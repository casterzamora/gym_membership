<?php

namespace Database\Factories;

use App\Models\Trainer;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainerFactory extends Factory
{
    protected $model = Trainer::class;

    public function definition(): array
    {
        $specializations = ['Yoga', 'Weightlifting', 'Cardio', 'HIIT', 'Pilates', 'Boxing', 'CrossFit', 'Swimming'];

        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'specialization' => $this->faker->randomElement($specializations),
            'hourly_rate' => $this->faker->numberBetween(30, 150),
        ];
    }
}
