<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update class_schedules to cascade delete on fitness_classes
        Schema::table('class_schedules', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->foreign('class_id')
                ->references('id')
                ->on('fitness_classes')
                ->cascadeOnDelete();
        });

        // Update equipment_usage to cascade delete on class_schedules and equipment
        Schema::table('equipment_usage', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropForeign(['equipment_id']);
            
            $table->foreign('schedule_id')
                ->references('id')
                ->on('class_schedules')
                ->cascadeOnDelete();
            
            $table->foreign('equipment_id')
                ->references('id')
                ->on('equipment')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_schedules', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->foreign('class_id')
                ->references('id')
                ->on('fitness_classes');
        });

        Schema::table('equipment_usage', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropForeign(['equipment_id']);
            
            $table->foreign('schedule_id')
                ->references('id')
                ->on('class_schedules');
            
            $table->foreign('equipment_id')
                ->references('id')
                ->on('equipment');
        });
    }
};
