<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            // First, make sure user_id is nullable so trainers don't need users
            if (Schema::hasColumn('trainers', 'user_id')) {
                Schema::table('trainers', function ($table) {
                    // Check if the column currently allows null
                    DB::statement('ALTER TABLE trainers MODIFY user_id BIGINT UNSIGNED NULL');
                });
            }

            // Get all foreign key constraints referencing trainer_id
            $database = DB::getDatabaseName();

            // Find and drop all foreign keys on fitness_classes.trainer_id
            $fitnessClassesFK = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS 
                WHERE TABLE_NAME = 'fitness_classes' 
                AND CONSTRAINT_SCHEMA = ?
            ", [$database]);

            foreach ($fitnessClassesFK as $fk) {
                DB::statement("ALTER TABLE fitness_classes DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            }

            // Find and drop all foreign keys on trainer_certifications.trainer_id
            $certFK = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS 
                WHERE TABLE_NAME = 'trainer_certifications' 
                AND CONSTRAINT_SCHEMA = ?
            ", [$database]);

            foreach ($certFK as $fk) {
                DB::statement("ALTER TABLE trainer_certifications DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            }

            // Add new foreign keys WITH CASCADE DELETE
            DB::statement('
                ALTER TABLE fitness_classes 
                ADD CONSTRAINT fitness_classes_trainer_id_foreign 
                FOREIGN KEY (trainer_id) 
                REFERENCES trainers(id) 
                ON DELETE CASCADE
            ');

            DB::statement('
                ALTER TABLE trainer_certifications 
                ADD CONSTRAINT trainer_certifications_trainer_id_foreign 
                FOREIGN KEY (trainer_id) 
                REFERENCES trainers(id) 
                ON DELETE CASCADE
            ');

        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            // Drop the cascade delete constraints
            DB::statement('ALTER TABLE fitness_classes DROP FOREIGN KEY fitness_classes_trainer_id_foreign');
            DB::statement('ALTER TABLE trainer_certifications DROP FOREIGN KEY trainer_certifications_trainer_id_foreign');

            // Restore original constraints without cascade delete
            DB::statement('
                ALTER TABLE fitness_classes 
                ADD CONSTRAINT fitness_classes_trainer_id_foreign 
                FOREIGN KEY (trainer_id) 
                REFERENCES trainers(id)
            ');

            DB::statement('
                ALTER TABLE trainer_certifications 
                ADD CONSTRAINT trainer_certifications_trainer_id_foreign 
                FOREIGN KEY (trainer_id) 
                REFERENCES trainers(id)
            ');

        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
};
