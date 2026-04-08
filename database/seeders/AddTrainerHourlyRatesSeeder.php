<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trainer;

class AddTrainerHourlyRatesSeeder extends Seeder
{
    public function run(): void
    {
        $trainers = Trainer::all();
        foreach ($trainers as $i => $trainer) {
            $trainer->update([
                'hourly_rate' => 50 + ($i * 10)
            ]);
        }
        $this->command->info('Added hourly rates to ' . count($trainers) . ' trainers.');
    }
}
