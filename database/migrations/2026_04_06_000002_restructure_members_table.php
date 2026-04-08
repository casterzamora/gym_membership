<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if user_id column exists before trying to drop it
        if (Schema::hasColumn('members', 'user_id')) {
            Schema::table('members', function (Blueprint $table) {
                // Get the foreign key constraints to identify the correct name
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                }
                $table->dropColumn('user_id');
            });
        }

        Schema::table('members', function (Blueprint $table) {
            // Add new fields
            $table->string('email')->unique()->after('last_name');
            $table->string('username')->unique()->after('email');
            $table->string('password_hash')->after('username');
            $table->string('fitness_goal')->nullable()->after('phone');
            $table->text('health_notes')->nullable()->after('fitness_goal');
            $table->string('registration_type')->default('standard')->after('health_notes');
            $table->date('membership_start')->nullable()->after('plan_id');
            $table->date('membership_end')->nullable()->after('membership_start');
            $table->string('membership_status')->default('active')->after('membership_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn([
                'email', 'username', 'password_hash', 
                'fitness_goal', 'health_notes', 'registration_type',
                'membership_start', 'membership_end', 'membership_status'
            ]);
        });

        Schema::table('members', function (Blueprint $table) {
            // Re-add user_id
            $table->foreignId('user_id')->constrained('users');
        });
    }
};
