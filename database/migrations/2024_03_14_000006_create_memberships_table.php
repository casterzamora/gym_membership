<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('membership_plans')->onDelete('restrict');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_months');
            $table->decimal('price', 10, 2);
            $table->enum('status', ['active', 'expired', 'cancelled', 'paused'])->default('active');
            $table->boolean('auto_renew')->default(true);
            $table->date('renewal_date')->nullable();
            $table->integer('classes_used_this_month')->default(0);
            $table->timestamps();
            $table->index(['member_id', 'status']);
            $table->index('end_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('memberships');
    }
};
