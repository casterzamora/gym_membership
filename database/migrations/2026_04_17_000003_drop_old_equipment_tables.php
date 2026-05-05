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
        // Drop equipment_usage table first (has FK to equipment)
        if (Schema::hasTable('equipment_usage')) {
            Schema::dropIfExists('equipment_usage');
        }

        // Keep class_equipment because the app still uses it for class/equipment mapping.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't recreate the old tables on rollback - they're being replaced
        // If needed, the old tables can be restored from a backup
    }
};
