<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Remove redundant auth/contact fields from members and trainers tables
     * since these are now stored in the unified users table.
     *
     * This enforces single-source-of-truth for user identity data.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Drop redundant authentication/contact fields
            // These are now authoritative in users table
            if (Schema::hasColumn('members', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('members', 'username')) {
                $table->dropColumn('username');
            }
            if (Schema::hasColumn('members', 'password_hash')) {
                $table->dropColumn('password_hash');
            }
        });

        Schema::table('trainers', function (Blueprint $table) {
            // Drop redundant contact field
            // Now authoritative in users table
            if (Schema::hasColumn('trainers', 'email')) {
                $table->dropColumn('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('email')->unique()->after('last_name');
            $table->string('username')->unique()->after('email');
            $table->string('password_hash')->after('username');
        });

        Schema::table('trainers', function (Blueprint $table) {
            $table->string('email')->unique()->after('last_name');
        });
    }
};
