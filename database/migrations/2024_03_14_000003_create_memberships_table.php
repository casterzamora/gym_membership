<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trainers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('specialization');
            $table->string('certification');
            $table->date('certification_expiry');
            $table->integer('years_experience')->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->text('bio')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('trainers');
    }
};
