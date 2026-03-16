<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->date('maintenance_date');
            $table->enum('maintenance_type', ['preventive', 'corrective', 'emergency']);
            $table->text('description');
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('performed_by');
            $table->date('next_due_date')->nullable();
            $table->timestamps();
            $table->index('equipment_id');
            $table->index('maintenance_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('maintenance_logs');
    }
};
