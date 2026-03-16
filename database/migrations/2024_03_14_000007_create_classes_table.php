<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('trainer_id')->constrained('trainers')->onDelete('restrict');
            $table->foreignId('area_id')->constrained('areas')->onDelete('restrict');
            $table->string('category');
            $table->integer('capacity');
            $table->integer('duration_minutes');
            $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->enum('schedule_type', ['recurring', 'one-time'])->default('recurring');
            $table->enum('status', ['active', 'cancelled', 'suspended'])->default('active');
            $table->timestamps();
            $table->index('trainer_id');
            $table->index('area_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('classes');
    }
};
