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

            // Find and drop class_schedules.class_id foreign key
            $classFK = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS 
                WHERE TABLE_NAME = 'class_schedules' 
                AND CONSTRAINT_SCHEMA = ?
            ", [$database]);

            foreach ($classFK as $fk) {
                DB::statement("ALTER TABLE class_schedules DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            }

            // Add new foreign key for class_id WITH CASCADE DELETE
            DB::statement('
                ALTER TABLE class_schedules 
                ADD CONSTRAINT class_schedules_class_id_foreign 
                FOREIGN KEY (class_id) 
                REFERENCES fitness_classes(id) 
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
            // Drop the CASCADE constraint
            DB::statement('ALTER TABLE class_schedules DROP FOREIGN KEY class_schedules_class_id_foreign');

            // Restore without cascade
            DB::statement('
                ALTER TABLE class_schedules 
                ADD CONSTRAINT class_schedules_class_id_foreign 
                FOREIGN KEY (class_id) 
                REFERENCES fitness_classes(id)
            ');

        } finally {
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }
    }
};
