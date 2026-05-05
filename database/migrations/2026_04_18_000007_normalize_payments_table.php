<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Normalize payments table:
     * 1. Add user_id reference for unified user tracking
     * 2. Ensure proper indexing for performance
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add user_id for unified tracking if not exists
            if (!Schema::hasColumn('payments', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('member_id')
                    ->constrained('users')
                    ->onDelete('cascade');
            }
        });

        // Ensure proper indexing
        Schema::table('payments', function (Blueprint $table) {
            // Check if indexes exist before adding
            $indexes = DB::select("SHOW INDEXES FROM payments");
            $indexNames = array_column($indexes, 'Key_name');

            if (!in_array('payments_payment_method_id_index', $indexNames)) {
                $table->index('payment_method_id');
            }
            if (!in_array('payments_member_id_index', $indexNames)) {
                $table->index('member_id');
            }
            if (!in_array('payments_user_id_index', $indexNames)) {
                $table->index('user_id');
            }
            if (!in_array('payments_payment_date_index', $indexNames)) {
                $table->index('payment_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop new columns and indexes
            if (Schema::hasColumn('payments', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
