<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('membership_plans')->updateOrInsert(
            ['plan_name' => 'Bronze'],
            ['price' => 750, 'duration_months' => 1, 'description' => 'Perfect for beginners']
        );

        DB::table('membership_plans')->updateOrInsert(
            ['plan_name' => 'Silver'],
            ['price' => 1000, 'duration_months' => 2, 'description' => 'For regular gym-goers']
        );

        DB::table('membership_plans')->updateOrInsert(
            ['plan_name' => 'Gold'],
            ['price' => 1500, 'duration_months' => 3, 'description' => 'Premium membership']
        );
    }

    public function down(): void
    {
        DB::table('membership_plans')->where('plan_name', 'Bronze')->update(['duration_months' => 1]);
        DB::table('membership_plans')->where('plan_name', 'Silver')->update(['duration_months' => 1]);
        DB::table('membership_plans')->where('plan_name', 'Gold')->update(['duration_months' => 1]);
    }
};
