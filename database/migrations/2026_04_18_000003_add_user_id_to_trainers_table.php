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
        if (Schema::hasColumn('trainers', 'user_id')) {
            return;
        }

        Schema::table('trainers', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')
                ->nullable()
                ->after('id');

            $table->foreign('user_id', 'trainers_user_id_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainers', function (Blueprint $table) {
            if (Schema::hasColumn('trainers', 'user_id')) {
                $table->dropForeign('trainers_user_id_foreign');
                $table->dropColumn('user_id');
            }
        });
    }
};
