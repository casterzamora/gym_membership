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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'checkout_token_hash')) {
                $table->string('checkout_token_hash')->nullable()->after('email_verification_token');
            }

            if (!Schema::hasColumn('users', 'checkout_token_expires_at')) {
                $table->timestamp('checkout_token_expires_at')->nullable()->after('checkout_token_hash');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'completed', 'failed'])
                    ->default('pending')
                    ->after('coverage_end');
            }

            if (!Schema::hasColumn('payments', 'checkout_full_name')) {
                $table->string('checkout_full_name')->nullable()->after('payment_status');
            }

            if (!Schema::hasColumn('payments', 'checkout_email')) {
                $table->string('checkout_email')->nullable()->after('checkout_full_name');
            }

            if (!Schema::hasColumn('payments', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('checkout_email');
            }

            if (!Schema::hasColumn('payments', 'card_brand')) {
                $table->string('card_brand')->nullable()->after('payment_reference');
            }

            if (!Schema::hasColumn('payments', 'card_last4')) {
                $table->string('card_last4', 4)->nullable()->after('card_brand');
            }

            if (!Schema::hasColumn('payments', 'billing_address_line1')) {
                $table->string('billing_address_line1')->nullable()->after('card_last4');
            }

            if (!Schema::hasColumn('payments', 'billing_address_line2')) {
                $table->string('billing_address_line2')->nullable()->after('billing_address_line1');
            }

            if (!Schema::hasColumn('payments', 'billing_city')) {
                $table->string('billing_city')->nullable()->after('billing_address_line2');
            }

            if (!Schema::hasColumn('payments', 'billing_state')) {
                $table->string('billing_state')->nullable()->after('billing_city');
            }

            if (!Schema::hasColumn('payments', 'billing_postal_code')) {
                $table->string('billing_postal_code')->nullable()->after('billing_state');
            }

            if (!Schema::hasColumn('payments', 'billing_country')) {
                $table->string('billing_country')->nullable()->after('billing_postal_code');
            }

            if (!Schema::hasColumn('payments', 'payment_failure_reason')) {
                $table->text('payment_failure_reason')->nullable()->after('billing_country');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'checkout_token_expires_at')) {
                $table->dropColumn('checkout_token_expires_at');
            }

            if (Schema::hasColumn('users', 'checkout_token_hash')) {
                $table->dropColumn('checkout_token_hash');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            $dropColumns = [
                'payment_status',
                'checkout_full_name',
                'checkout_email',
                'payment_reference',
                'card_brand',
                'card_last4',
                'billing_address_line1',
                'billing_address_line2',
                'billing_city',
                'billing_state',
                'billing_postal_code',
                'billing_country',
                'payment_failure_reason',
            ];

            foreach ($dropColumns as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
