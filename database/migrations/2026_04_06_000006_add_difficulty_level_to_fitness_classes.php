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
            $table->string('difficulty_level')->default('Beginner')->after('max_participants');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fitness_classes', function (Blueprint $table) {
            $table->dropColumn('difficulty_level');
        });
    }
};
