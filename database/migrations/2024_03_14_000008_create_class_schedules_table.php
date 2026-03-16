<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->enum('day_of_week', ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->date('scheduled_date')->nullable();
            $table->boolean('is_cancelled')->default(false);
            $table->text('cancellation_reason')->nullable();
            $table->integer('current_enrollment')->default(0);
            $table->timestamps();
            $table->index('class_id');
            $table->index('scheduled_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_schedules');
    }
};
