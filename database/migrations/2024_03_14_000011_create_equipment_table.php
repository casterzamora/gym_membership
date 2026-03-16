<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->foreignId('area_id')->constrained('areas')->onDelete('restrict');
            $table->string('serial_number')->unique();
            $table->date('purchase_date');
            $table->decimal('purchase_cost', 10, 2)->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->enum('status', ['available', 'maintenance', 'damaged', 'retired'])->default('available');
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->integer('maintenance_interval_days')->default(30);
            $table->enum('condition', ['good', 'fair', 'poor'])->default('good');
            $table->timestamps();
            $table->index('area_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('equipment');
    }
};
