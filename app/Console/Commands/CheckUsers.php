<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckUsers extends Command
{
    protected $signature = 'check:users';
    protected $description = 'Check users in the database';

    public function handle()
    {
        $users = DB::table('users')->select('id', 'email', 'role')->orderBy('id')->get();

        $this->line("\n=== USERS IN DATABASE ===\n");

        foreach ($users as $user) {
            $this->line('ID: ' . $user->id . ' | Email: ' . $user->email . ' | Role: ' . ($user->role ?? 'NULL'));
        }

        $this->line("\n=== ADMIN USER CHECK ===");
        $admin = DB::table('users')->where('email', 'admin@gym.com')->first();
        if ($admin) {
            $this->line('Admin user found: ' . $admin->email . ' | Role: ' . ($admin->role ?? 'NULL'));
        } else {
            $this->line('Admin user NOT found');
        }

        $this->line("");
    }
}
