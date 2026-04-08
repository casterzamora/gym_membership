<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetDatabase extends Command
{
    protected $signature = 'db:reset-all';
    protected $description = 'Reset all database tables with foreign key checks disabled';

    public function handle()
    {
        $this->info('Disabling foreign key checks...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->info('Dropping all tables...');
        $tables = DB::select('SHOW TABLES');
        $db = env('DB_DATABASE');
        
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
            $this->line("Dropped table: {$tableName}");
        }

        $this->info('Re-enabling foreign key checks...');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('Running migrations...');
        $this->call('migrate');

        $this->info('Seeding database...');
        $this->call('db:seed');

        $this->info('Database reset complete!');
    }
}
