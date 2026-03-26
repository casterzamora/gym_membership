<?php

namespace Database\Factories;

use App\Models\Certification;
use Illuminate\Database\Eloquent\Factories\Factory;

class CertificationFactory extends Factory
{
    protected $model = Certification::class;

    public function definition(): array
    {
        static $counter = 0;
        $counter++;
        
        $certNames = ['CPR', 'NASM', 'ACE', 'ISSF', 'IYASA', 'AFAA', 'NFPT', 'USA Weightlifting'];

        return [
            'cert_name' => $certNames[$counter % count($certNames)] . '-' . $counter,
            'issuing_organization' => $this->faker->company(),
            'cert_number' => $this->faker->regexify('[A-Z]{3}[0-9]{6}'),
            'issue_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'expiry_date' => $this->faker->dateTimeBetween('now', '+5 years'),
        ];
    }
}
