<?php
/**
 * Data Migration: Populate Trainers with User Records
 * 
 * This script creates user records from existing trainers data
 * and populates trainers.user_id with the corresponding user IDs
 * 
 * Run AFTER running the schema migration (2026_04_18_000003)
 * php migrate_trainers_to_users.php
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TRAINERS TO USERS MIGRATION ===\n\n";

try {
    DB::beginTransaction();

    // Get all trainers that don't have a user_id yet
    $trainers = DB::table('trainers')
        ->whereNull('user_id')
        ->get();

    if ($trainers->isEmpty()) {
        echo "✓ All trainers already have user_id assigned!\n";
        DB::commit();
        exit(0);
    }

    echo "Found " . count($trainers) . " trainers without user_id\n\n";

    $created = 0;
    $updated = 0;
    $errors = [];

    foreach ($trainers as $trainer) {
        try {
            // Check if user with this email already exists
            $existingUser = DB::table('users')
                ->where('email', $trainer->email)
                ->first();

            $userId = null;

            if ($existingUser) {
                // Use existing user
                $userId = $existingUser->id;
                echo "[EXISTING] Using existing user #{$userId} for trainer #{$trainer->id} ({$trainer->email})\n";
            } else {
                // Create new user with trainer's data
                $userId = DB::table('users')->insertGetId([
                    'name' => $trainer->first_name . ' ' . $trainer->last_name,
                    'first_name' => $trainer->first_name,
                    'last_name' => $trainer->last_name,
                    'email' => $trainer->email,
                    'password' => Hash::make('temp-password-' . uniqid()),
                    'role' => 'trainer',
                    'phone' => $trainer->phone,
                    'specialization' => $trainer->specialization,
                    'hourly_rate' => $trainer->hourly_rate ?? 0,
                    'is_active' => 1,
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                echo "[CREATED] New user #{$userId} for trainer #{$trainer->id} ({$trainer->email})\n";
                $created++;
            }

            // Update trainer with user_id
            DB::table('trainers')
                ->where('id', $trainer->id)
                ->update(['user_id' => $userId]);

            $updated++;

        } catch (\Exception $e) {
            $errors[] = "Trainer #{$trainer->id} ({$trainer->email}): {$e->getMessage()}";
            echo "[ERROR] Trainer #{$trainer->id}: {$e->getMessage()}\n";
        }
    }

    DB::commit();

    echo "\n=== RESULTS ===\n";
    echo "Users Created: $created\n";
    echo "Trainers Updated: $updated\n";
    echo "Errors: " . count($errors) . "\n";

    if (!empty($errors)) {
        echo "\n--- ERRORS ---\n";
        foreach ($errors as $error) {
            echo "  - $error\n";
        }
    }

    echo "\n✓ Migration complete!\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n✗ MIGRATION FAILED: {$e->getMessage()}\n";
    echo "Stack: {$e->getTraceAsString()}\n";
    exit(1);
}
