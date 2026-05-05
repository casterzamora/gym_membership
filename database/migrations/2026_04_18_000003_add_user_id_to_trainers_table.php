<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Restores user_id foreign key to trainers table
     * Allows trainers to be linked to users for unified authentication
     */
    public function up(): void
    {
        Schema::table('trainers', function (Blueprint $table) {
            // Add user_id column if it doesn't exist
            if (!Schema::hasColumn('trainers', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->onDelete('cascade')
                    ->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainers', function (Blueprint $table) {
            if (Schema::hasColumn('trainers', 'user_id')) {
                $table->dropForeignKey(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
