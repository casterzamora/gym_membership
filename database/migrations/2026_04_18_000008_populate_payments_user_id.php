<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Populate user_id in payments table based on member_id relationship
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            // For each payment, populate user_id from the member's user_id
            $payments = DB::table('payments')
                ->whereNull('payments.user_id')
                ->join('members', 'payments.member_id', '=', 'members.id')
                ->select('payments.id', 'members.user_id')
                ->get();

            foreach ($payments as $payment) {
                DB::table('payments')
                    ->where('id', $payment->id)
                    ->update(['user_id' => $payment->user_id]);
            }

            echo "Updated " . count($payments) . " payments with user_id\n";
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('payments')->update(['user_id' => null]);
    }
};
