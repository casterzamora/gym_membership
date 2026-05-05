<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DATABASE SCHEMA ANALYSIS ===\n\n";

// 1. Get all tables
$tables = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE'");

echo "TABLES FOUND: " . count($tables) . "\n";
echo str_repeat("=", 80) . "\n\n";

foreach ($tables as $table) {
    $tableName = $table->TABLE_NAME;
    
    // Skip migrations tables
    if (in_array($tableName, ['migrations', 'personal_access_tokens'])) {
        continue;
    }
    
    echo "TABLE: $tableName\n";
    echo str_repeat("-", 80) . "\n";
    
    // Get columns
    $columns = DB::select("SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? ORDER BY ORDINAL_POSITION", [$tableName]);
    
    echo "Columns (" . count($columns) . "):\n";
    foreach ($columns as $col) {
        $key = $col->COLUMN_KEY === 'PRI' ? ' [PRIMARY]' : ($col->COLUMN_KEY === 'UNI' ? ' [UNIQUE]' : ($col->COLUMN_KEY === 'MUL' ? ' [INDEX]' : ''));
        $nullable = $col->IS_NULLABLE === 'YES' ? ' (nullable)' : '';
        echo "  - {$col->COLUMN_NAME}: {$col->COLUMN_TYPE}{$key}{$nullable}\n";
    }
    
    // Get foreign keys
    $fks = DB::select("
        SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL
    ", [$tableName]);
    
    if (!empty($fks)) {
        echo "\nForeign Keys:\n";
        foreach ($fks as $fk) {
            echo "  - {$fk->COLUMN_NAME} → {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
        }
    }
    
    // Get record count
    $count = DB::table($tableName)->count();
    echo "\nRecord Count: $count\n";
    
    echo "\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "END OF SCHEMA ANALYSIS\n";
