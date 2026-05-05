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
        Schema::table('equipment_tracking', function (Blueprint $table) {
            // Add returned_at if not exists
            if (!Schema::hasColumn('equipment_tracking', 'returned_at')) {
                $table->timestamp('returned_at')->nullable()->after('used_at');
            }

            // Add user_id if not exists (some environments have this column missing)
            if (!Schema::hasColumn('equipment_tracking', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('equipment_id');
            }
            
            // Add assigned_by if not exists
            if (!Schema::hasColumn('equipment_tracking', 'assigned_by')) {
                $table->unsignedBigInteger('assigned_by')->nullable()->after('returned_at');
            }
            
            // Add returned_by if not exists
            if (!Schema::hasColumn('equipment_tracking', 'returned_by')) {
                $table->unsignedBigInteger('returned_by')->nullable()->after('assigned_by');
            }
        });

        // Add foreign keys if they don't exist
        Schema::table('equipment_tracking', function (Blueprint $table) {
            // Add user_id foreign key if not exists
            $this->addForeignKeyIfNotExists($table, 'user_id');
            
            // Add assigned_by foreign key if not exists
            $this->addForeignKeyIfNotExists($table, 'assigned_by');
            
            // Add returned_by foreign key if not exists
            $this->addForeignKeyIfNotExists($table, 'returned_by');
        });

        // Add indexes
        Schema::table('equipment_tracking', function (Blueprint $table) {
            if (!$this->indexExists('equipment_tracking', 'equipment_tracking_user_id_index')) {
                $table->index('user_id');
            }
            if (!$this->indexExists('equipment_tracking', 'equipment_tracking_assigned_by_index')) {
                $table->index('assigned_by');
            }
            if (!$this->indexExists('equipment_tracking', 'equipment_tracking_returned_by_index')) {
                $table->index('returned_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_tracking', function (Blueprint $table) {
            $table->dropForeignKey(['user_id']);
            $table->dropForeignKey(['assigned_by']);
            $table->dropForeignKey(['returned_by']);
            
            $table->dropIndex(['user_id']);
            $table->dropIndex(['assigned_by']);
            $table->dropIndex(['returned_by']);

            $table->dropColumn(['returned_at', 'assigned_by', 'returned_by']);
        });
    }

    /**
     * Add a foreign key if it doesn't already exist
     */
    private function addForeignKeyIfNotExists($table, $column): void
    {
        // Only add MySQL-style foreign keys when the connection driver supports them
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        if (!$this->foreignKeyExists('equipment_tracking', $column)) {
            $table->foreign($column)
                ->references('id')->on('users')
                ->nullOnDelete();
        }
    }

    /**
     * Check if a foreign key already exists
     */
    private function foreignKeyExists($table, $column): bool
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return false;
        }

        $keyInfo = \Illuminate\Support\Facades\DB::table('INFORMATION_SCHEMA.KEY_COLUMN_USAGE')
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->first();
        
        return !empty($keyInfo);
    }

    /**
     * Check if an index already exists
     */
    private function indexExists($table, $indexName): bool
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return false;
        }

        $indexInfo = \Illuminate\Support\Facades\DB::table('INFORMATION_SCHEMA.STATISTICS')
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $indexName)
            ->first();
        
        return !empty($indexInfo);
    }
};
