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
        Schema::table('fitness_classes', function (Blueprint $table) {
            // Drop the existing foreign key
            $table->dropForeign(['trainer_id']);
            
            // Add it back with cascade delete
            $table->foreign('trainer_id')
                ->references('id')
                ->on('trainers')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fitness_classes', function (Blueprint $table) {
            // Drop the cascade delete foreign key
            $table->dropForeign(['trainer_id']);
            
            // Add it back without cascade delete
            $table->foreign('trainer_id')
                ->references('id')
                ->on('trainers');
        });
    }
};
