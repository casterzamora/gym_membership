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
        // Add payment_method_id if it doesn't exist
        if (!Schema::hasColumn('payments', 'payment_method_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedBigInteger('payment_method_id')->nullable()->after('payment_date');
            });

            // Add foreign key constraint
            Schema::table('payments', function (Blueprint $table) {
                $table->foreign('payment_method_id')
                    ->references('payment_method_id')
                    ->on('payment_methods')
                    ->onDelete('set null');
            });
        }

        // Migrate data from payment_method enum to payment_methods table if it exists
        if (Schema::hasColumn('payments', 'payment_method')) {
            $this->migratePaymentMethodData();

            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('payment_method');
            });
        }

        // Drop status field if it still exists
        if (Schema::hasColumn('payments', 'status')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'payment_method_id')) {
                $table->dropForeign(['payment_method_id']);
                $table->dropColumn('payment_method_id');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_method', ['Cash', 'Card', 'Bank Transfer'])->default('Cash')->after('payment_date');
            $table->enum('status', ['Completed', 'Pending', 'Failed'])->default('Completed');
        });
    }

    private function migratePaymentMethodData(): void
    {
        $payments = DB::table('payments')->get();
        foreach ($payments as $payment) {
            if ($payment->payment_method) {
                $method = DB::table('payment_methods')
                    ->where('method_name', $payment->payment_method)
                    ->first();

                if ($method) {
                    DB::table('payments')
                        ->where('id', $payment->id)
                        ->update(['payment_method_id' => $method->payment_method_id]);
                }
            }
        }
    }
};
