<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Migrates existing trainer data into users table and links trainers via user_id
     * Strategy: For each trainer, either:
     *   1. Find existing user with matching email
     *   2. Create new user from trainer data if no user exists
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        try {
            // Get all trainers that don't already have a user_id
            $trainers = DB::table('trainers')->whereNull('user_id')->get();
            
            foreach ($trainers as $trainer) {
                // Try to find existing user by email
                $user = DB::table('users')
                    ->where('email', $trainer->email)
                    ->first();
                
                if ($user) {
                    // Link existing user to trainer
                    // Update user with trainer-specific fields
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'specialization' => $trainer->specialization,
                            'hourly_rate' => $trainer->hourly_rate,
                        ]);
                    
                    DB::table('trainers')
                        ->where('id', $trainer->id)
                        ->update(['user_id' => $user->id]);
                } else {
                    // Create new user from trainer data
                    $newUserId = DB::table('users')->insertGetId([
                        'name' => trim($trainer->first_name . ' ' . $trainer->last_name),
                        'first_name' => $trainer->first_name,
                        'last_name' => $trainer->last_name,
                        'email' => $trainer->email,
                        'password' => \Hash::make('temporary-password-' . uniqid()),
                        'phone' => $trainer->phone,
                        'specialization' => $trainer->specialization,
                        'hourly_rate' => $trainer->hourly_rate,
                        'role' => 'trainer',
                        'is_active' => true,
                        'created_at' => $trainer->created_at ?? now(),
                        'updated_at' => $trainer->updated_at ?? now(),
                    ]);
                    
                    // Link new user to trainer
                    DB::table('trainers')
                        ->where('id', $trainer->id)
                        ->update(['user_id' => $newUserId]);
                }
            }
            
            echo "Migrated " . count($trainers) . " trainers to users table\n";
            
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear user_id from trainers that were created during data migration
        // Note: This doesn't delete the created users, just unlinks them
        DB::table('trainers')->update(['user_id' => null]);
    }
};
