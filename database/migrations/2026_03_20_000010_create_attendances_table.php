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
        Schema::create('attendance', function (Blueprint $table) {
            $table->foreignId('member_id')->constrained('members');
            $table->foreignId('schedule_id')->constrained('class_schedules');
            $table->enum('attendance_status', ['Present', 'Absent', 'Late'])->default('Present');
            $table->timestamp('recorded_at');
            $table->timestamps();

            // Composite primary key - prevents duplicate attendance records
            $table->primary(['member_id', 'schedule_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
