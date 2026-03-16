<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('class_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained('class_schedules')->onDelete('cascade');
            $table->dateTime('enrollment_date');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->string('cancellation_reason')->nullable();
            $table->boolean('attended')->default(false);
            $table->timestamps();
            $table->index('member_id');
            $table->index('schedule_id');
            $table->unique(['member_id', 'schedule_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_enrollments');
    }
};
