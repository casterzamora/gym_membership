<?php
/**
 * Data Migration: Populate Members with User Records
 * 
 * This script creates user records from existing members data
 * and populates members.user_id with the corresponding user IDs
 * 
 * Run AFTER running the schema migration (2026_04_18_000002)
 * php migrate_members_to_users.php
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== MEMBERS TO USERS MIGRATION ===\n\n";

try {
    DB::beginTransaction();

    // Get all members that don't have a user_id yet
    $members = DB::table('members')
        ->whereNull('user_id')
        ->get();

    if ($members->isEmpty()) {
        echo "✓ All members already have user_id assigned!\n";
        DB::commit();
        exit(0);
    }

    echo "Found " . count($members) . " members without user_id\n\n";

    $created = 0;
    $updated = 0;
    $errors = [];

    foreach ($members as $member) {
        try {
            // Check if user with this email already exists
            $existingUser = DB::table('users')
                ->where('email', $member->email)
                ->first();

            $userId = null;

            if ($existingUser) {
                // Use existing user
                $userId = $existingUser->id;
                echo "[EXISTING] Using existing user #{$userId} for member #{$member->id} ({$member->email})\n";
            } else {
                // Create new user with member's data
                $userId = DB::table('users')->insertGetId([
                    'name' => $member->first_name . ' ' . $member->last_name,
                    'first_name' => $member->first_name,
                    'last_name' => $member->last_name,
                    'email' => $member->email,
                    'password' => Hash::make($member->password_hash ?? 'temp-password-' . uniqid()),
                    'role' => 'member',
                    'phone' => $member->phone,
                    'is_active' => $member->membership_status === 'active' ? 1 : 0,
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                echo "[CREATED] New user #{$userId} for member #{$member->id} ({$member->email})\n";
                $created++;
            }

            // Update member with user_id
            DB::table('members')
                ->where('id', $member->id)
                ->update(['user_id' => $userId]);

            $updated++;

        } catch (\Exception $e) {
            $errors[] = "Member #{$member->id} ({$member->email}): {$e->getMessage()}";
            echo "[ERROR] Member #{$member->id}: {$e->getMessage()}\n";
        }
    }

    DB::commit();

    echo "\n=== RESULTS ===\n";
    echo "Users Created: $created\n";
    echo "Members Updated: $updated\n";
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
