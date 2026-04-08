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
        Schema::table('trainers', function (Blueprint $table) {
            // Add hourly_rate if it doesn't exist
            if (!Schema::hasColumn('trainers', 'hourly_rate')) {
                $table->decimal('hourly_rate', 8, 2)->default(0)->after('phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainers', function (Blueprint $table) {
            if (Schema::hasColumn('trainers', 'hourly_rate')) {
                $table->dropColumn('hourly_rate');
            }
        });
    }
};
