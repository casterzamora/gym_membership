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
        \Log::info('Starting equipment tracking data migration...');

        // Step 1: Migrate class_equipment to equipment_tracking
        // These represent required equipment for classes
        try {
            if (Schema::hasTable('class_equipment')) {
                $classEquipmentCount = DB::table('class_equipment')->count();
                \Log::info("Migrating $classEquipmentCount class equipment records...");

                $classEquipment = DB::table('class_equipment')->get();
                foreach ($classEquipment as $ce) {
                    // Check if already exists
                    $exists = DB::table('equipment_tracking')
                        ->where('class_id', $ce->class_id)
                        ->where('equipment_id', $ce->equipment_id)
                        ->where('status', 'required')
                        ->whereNull('used_at')
                        ->exists();

                    if (!$exists) {
                        DB::table('equipment_tracking')->insert([
                            'class_id' => $ce->class_id,
                            'equipment_id' => $ce->equipment_id,
                            'quantity' => 1,
                            'status' => 'required',
                            'used_at' => null,
                            'notes' => 'Migrated from class_equipment',
                            'created_at' => $ce->created_at ?? now(),
                            'updated_at' => $ce->updated_at ?? now(),
                        ]);
                    }
                }
                \Log::info("✓ Migrated class_equipment records");
            } else {
                \Log::info("class_equipment table not found - skipping migration");
            }
        } catch (\Exception $e) {
            \Log::error("✗ Failed to migrate class_equipment: " . $e->getMessage());
            throw $e;
        }

        // Step 2: Migrate equipment_usage to equipment_tracking
        // These represent actual equipment usage during sessions
        try {
            if (Schema::hasTable('equipment_usage')) {
                $equipmentUsageCount = DB::table('equipment_usage')->count();
                \Log::info("Migrating $equipmentUsageCount equipment usage records...");

                $equipmentUsage = DB::table('equipment_usage')->get();
                foreach ($equipmentUsage as $eu) {
                    // Get the schedule to find the associated class
                    $schedule = DB::table('class_schedules')
                        ->where('id', $eu->schedule_id)
                        ->first();

                    if (!$schedule) {
                        \Log::warning("Schedule not found for equipment_usage {$eu->id}");
                        continue;
                    }

                    // Check if this usage record already exists
                    $exists = DB::table('equipment_tracking')
                        ->where('class_id', $schedule->class_id)
                        ->where('equipment_id', $eu->equipment_id)
                        ->where('status', 'in_use')
                        ->where('used_at', $eu->created_at)
                        ->exists();

                    if (!$exists) {
                        DB::table('equipment_tracking')->insert([
                            'class_id' => $schedule->class_id,
                            'equipment_id' => $eu->equipment_id,
                            'quantity' => 1,
                            'status' => 'in_use',
                            'used_at' => $eu->created_at,
                            'notes' => "Migrated from equipment_usage (duration: {$eu->usage_duration} min)",
                            'created_at' => $eu->created_at,
                            'updated_at' => $eu->updated_at,
                        ]);
                    }
                }
                \Log::info("✓ Migrated equipment_usage records");
            } else {
                \Log::info("equipment_usage table not found - skipping migration");
            }
        } catch (\Exception $e) {
            \Log::error("✗ Failed to migrate equipment_usage: " . $e->getMessage());
            throw $e;
        }

        $totalCount = DB::table('equipment_tracking')->count();
        \Log::info("✓ Data migration complete! Total equipment_tracking records: $totalCount");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Log::info('Rolling back equipment tracking data migration...');
        
        // Clear all migrated data from equipment_tracking
        DB::table('equipment_tracking')
            ->where('notes', 'like', 'Migrated%')
            ->delete();

        \Log::info('✓ Equipment tracking data rollback complete');
    }
};
