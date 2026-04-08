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
        // Check if user_id column exists before trying to drop it
        if (Schema::hasColumn('trainers', 'user_id')) {
            Schema::table('trainers', function (Blueprint $table) {
                // Try to drop the foreign key
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                }
                $table->dropColumn(['user_id', 'hourly_rate']);
            });
        }

        Schema::table('trainers', function (Blueprint $table) {
            // Add email field
            $table->string('email')->unique()->after('last_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainers', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        Schema::table('trainers', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('hourly_rate', 8, 2)->default(0);
        });
    }
};
