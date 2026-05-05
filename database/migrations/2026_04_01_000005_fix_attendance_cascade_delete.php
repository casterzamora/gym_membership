<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        // Skip entirely on non-mysql drivers
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
            return;
        }

        try {
            $database = DB::getDatabaseName();

            // Find and drop attendance.schedule_id foreign key
            $attendanceFK = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS 
                WHERE TABLE_NAME = 'attendance' 
                AND CONSTRAINT_SCHEMA = ?
            ", [$database]);

            foreach ($attendanceFK as $fk) {
                DB::statement("ALTER TABLE attendance DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            }

            // Add new foreign keys WITH CASCADE DELETE
            // attendance.member_id -> members (CASCADE)
            DB::statement('
                ALTER TABLE attendance 
                ADD CONSTRAINT attendance_member_id_foreign 
                FOREIGN KEY (member_id) 
                REFERENCES members(id) 
                ON DELETE CASCADE
            ');

            // attendance.schedule_id -> class_schedules (CASCADE)
            DB::statement('
                ALTER TABLE attendance 
                ADD CONSTRAINT attendance_schedule_id_foreign 
                FOREIGN KEY (schedule_id) 
                REFERENCES class_schedules(id) 
                ON DELETE CASCADE
            ');

        } finally {
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        try {
            // Drop CASCADE constraints
            DB::statement('ALTER TABLE attendance DROP FOREIGN KEY attendance_member_id_foreign');
            DB::statement('ALTER TABLE attendance DROP FOREIGN KEY attendance_schedule_id_foreign');

            // Restore without cascade
            DB::statement('
                ALTER TABLE attendance 
                ADD CONSTRAINT attendance_member_id_foreign 
                FOREIGN KEY (member_id) 
                REFERENCES members(id)
            ');

            DB::statement('
                ALTER TABLE attendance 
                ADD CONSTRAINT attendance_schedule_id_foreign 
                FOREIGN KEY (schedule_id) 
                REFERENCES class_schedules(id)
            ');

        } finally {
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }
    }
};
