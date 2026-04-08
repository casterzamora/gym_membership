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
        // Drop and recreate attendance foreign key with cascadeOnDelete
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->foreign('member_id')
                ->references('id')
                ->on('members')
                ->cascadeOnDelete();
        });

        // Drop and recreate payments foreign key with cascadeOnDelete
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->foreign('member_id')
                ->references('id')
                ->on('members')
                ->cascadeOnDelete();
        });

        // Drop and recreate membership_upgrades foreign key with cascadeOnDelete
        Schema::table('membership_upgrades', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->foreign('member_id')
                ->references('id')
                ->on('members')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore original foreign keys without cascadeOnDelete
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->foreign('member_id')->references('id')->on('members');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->foreign('member_id')->references('id')->on('members');
        });

        Schema::table('membership_upgrades', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->foreign('member_id')->references('id')->on('members');
        });
    }
};
