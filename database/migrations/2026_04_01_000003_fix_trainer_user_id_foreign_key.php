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

            // Find and drop the user_id foreign key on trainers
            $userFK = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS 
                WHERE TABLE_NAME = 'trainers' 
                AND CONSTRAINT_SCHEMA = ?
            ", [$database]);

            foreach ($userFK as $fk) {
                DB::statement("ALTER TABLE trainers DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            }

            // Add new foreign key for user_id WITH SET NULL on delete (trainers can exist without users)
            DB::statement('
                ALTER TABLE trainers 
                ADD CONSTRAINT trainers_user_id_foreign 
                FOREIGN KEY (user_id) 
                REFERENCES users(id) 
                ON DELETE SET NULL
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
            // Drop the SET NULL constraint
            DB::statement('ALTER TABLE trainers DROP FOREIGN KEY trainers_user_id_foreign');

            // Restore original constraint
            DB::statement('
                ALTER TABLE trainers 
                ADD CONSTRAINT trainers_user_id_foreign 
                FOREIGN KEY (user_id) 
                REFERENCES users(id)
            ');

        } finally {
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }
    }
};
