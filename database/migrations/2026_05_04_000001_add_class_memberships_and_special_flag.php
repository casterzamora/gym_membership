<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fitness_classes', function (Blueprint $table) {
            if (!Schema::hasColumn('fitness_classes', 'is_special')) {
                $table->boolean('is_special')->default(false)->after('difficulty_level');
            }
        });

        Schema::create('class_memberships', function (Blueprint $table) {
            $table->foreignId('class_id')->constrained('fitness_classes')->cascadeOnDelete();
            $table->foreignId('membership_plan_id')->constrained('membership_plans')->cascadeOnDelete();
            $table->primary(['class_id', 'membership_plan_id']);
        });

        $planIds = DB::table('membership_plans')->pluck('id');
        $classIds = DB::table('fitness_classes')->pluck('id');

        foreach ($classIds as $classId) {
            foreach ($planIds as $planId) {
                DB::table('class_memberships')->updateOrInsert([
                    'class_id' => $classId,
                    'membership_plan_id' => $planId,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('class_memberships');

        if (Schema::hasColumn('fitness_classes', 'is_special')) {
            Schema::table('fitness_classes', function (Blueprint $table) {
                $table->dropColumn('is_special');
            });
        }
    }
};
