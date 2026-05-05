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
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $database = DB::getDatabaseName();

        $exists = DB::selectOne(
            "SELECT COUNT(*) AS cnt
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = 'trainer_certifications'
               AND COLUMN_NAME = 'certification_id'
               AND REFERENCED_TABLE_NAME = 'certifications'",
            [$database]
        );

        if ((int) ($exists->cnt ?? 0) === 0) {
            DB::statement(
                'ALTER TABLE trainer_certifications
                 ADD CONSTRAINT trainer_certifications_certification_id_foreign
                 FOREIGN KEY (certification_id)
                 REFERENCES certifications(id)
                 ON DELETE CASCADE'
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $database = DB::getDatabaseName();

        $exists = DB::selectOne(
            "SELECT COUNT(*) AS cnt
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = 'trainer_certifications'
               AND CONSTRAINT_NAME = 'trainer_certifications_certification_id_foreign'
               AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [$database]
        );

        if ((int) ($exists->cnt ?? 0) > 0) {
            DB::statement('ALTER TABLE trainer_certifications DROP FOREIGN KEY trainer_certifications_certification_id_foreign');
        }
    }
};
