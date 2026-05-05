<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Migrates existing member data into users table and links members via user_id
     * Strategy: For each member, either:
     *   1. Find existing user with matching email
     *   2. Create new user from member data if no user exists
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        try {
            // Get all members that don't already have a user_id
            $members = DB::table('members')->whereNull('user_id')->get();
            
            foreach ($members as $member) {
                // Try to find existing user by email
                $user = DB::table('users')
                    ->where('email', $member->email)
                    ->first();
                
                if ($user) {
                    // Link existing user to member
                    DB::table('members')
                        ->where('id', $member->id)
                        ->update(['user_id' => $user->id]);
                } else {
                    // Create new user from member data
                    $newUserId = DB::table('users')->insertGetId([
                        'name' => trim($member->first_name . ' ' . $member->last_name),
                        'first_name' => $member->first_name,
                        'last_name' => $member->last_name,
                        'email' => $member->email,
                        'password' => $member->password_hash, // Already hashed in member table
                        'phone' => $member->phone,
                        'role' => 'member',
                        'is_active' => $member->membership_status === 'active',
                        'created_at' => $member->created_at ?? now(),
                        'updated_at' => $member->updated_at ?? now(),
                    ]);
                    
                    // Link new user to member
                    DB::table('members')
                        ->where('id', $member->id)
                        ->update(['user_id' => $newUserId]);
                }
            }
            
            echo "Migrated " . count($members) . " members to users table\n";
            
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear user_id from members that were created during data migration
        // Note: This doesn't delete the created users, just unlinks them
        DB::table('members')->update(['user_id' => null]);
    }
};
