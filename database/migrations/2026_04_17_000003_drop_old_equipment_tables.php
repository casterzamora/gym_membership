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

        // Drop class_equipment table (has FK to equipment and fitness_classes)
        if (Schema::hasTable('class_equipment')) {
            Schema::dropIfExists('class_equipment');
        }
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
