<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained('class_schedules')->onDelete('cascade');
            $table->dateTime('check_in_time');
            $table->dateTime('check_out_time')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('marked_by_trainer')->nullable();
            $table->timestamps();
            $table->index(['member_id', 'schedule_id']);
            $table->index('check_in_time');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance');
    }
};
