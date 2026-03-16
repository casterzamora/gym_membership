<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('membership_id')->nullable()->constrained('memberships')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_type', ['membership', 'renewal', 'additional_service'])->default('membership');
            $table->enum('payment_method', ['credit_card', 'debit_card', 'bank_transfer', 'cash'])->default('credit_card');
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('transaction_id')->unique()->nullable();
            $table->dateTime('payment_date');
            $table->date('due_date')->nullable();
            $table->string('receipt_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('member_id');
            $table->index('payment_date');
            $table->index('payment_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
