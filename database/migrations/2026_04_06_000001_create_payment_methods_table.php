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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id('payment_method_id');
            $table->string('method_name')->unique();
            $table->timestamps();
        });

        // Seed payment methods
        DB::table('payment_methods')->insert([
            ['method_name' => 'Cash'],
            ['method_name' => 'Credit Card'],
            ['method_name' => 'Debit Card'],
            ['method_name' => 'Bank Transfer'],
            ['method_name' => 'GCash'],
            ['method_name' => 'PayMaya'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
