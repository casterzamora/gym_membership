<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Get all tables
$tables = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME");

$output = "DATABASE SCHEMA ANALYSIS\n";
$output .= str_repeat("=", 100) . "\n\n";

$coreTablesInfo = [];

foreach ($tables as $table) {
    $tableName = $table->TABLE_NAME;
    
    // Skip system tables
    if (in_array($tableName, ['migrations', 'personal_access_tokens', 'cache', 'cache_locks', 'failed_jobs', 'jobs', 'job_batches', 'password_reset_tokens'])) {
        continue;
    }
    
    $columns = DB::select("SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? ORDER BY ORDINAL_POSITION", [$tableName]);
    
    $fks = DB::select("
        SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL
        ORDER BY COLUMN_NAME
    ", [$tableName]);
    
    $count = DB::table($tableName)->count();
    
    $coreTablesInfo[$tableName] = [
        'columns' => $columns,
        'fks' => $fks,
        'count' => $count,
        'column_count' => count($columns)
    ];
    
    $output .= "$tableName ($count records, " . count($columns) . " columns)\n";
    $output .= str_repeat("-", 100) . "\n";
    
    // List columns
    foreach ($columns as $col) {
        $key = $col->COLUMN_KEY === 'PRI' ? ' [PK]' : '';
        $nullable = $col->IS_NULLABLE === 'YES' ? ' NULL' : ' NOT NULL';
        $output .= "  {$col->COLUMN_NAME}: {$col->COLUMN_TYPE}$key$nullable\n";
    }
    
    // List FKs
    if (!empty($fks)) {
        $output .= "  Foreign Keys:\n";
        foreach ($fks as $fk) {
            $output .= "    {$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
        }
    }
    
    $output .= "\n";
}

file_put_contents('schema_output.txt', $output);
echo $output;

// Now analyze for redundancies
echo "\n\n" . str_repeat("=", 100) . "\n";
echo "REDUNDANCY ANALYSIS\n";
echo str_repeat("=", 100) . "\n\n";

// Check for duplicate column names across tables
$allColumns = [];
foreach ($coreTablesInfo as $tableName => $info) {
    foreach ($info['columns'] as $col) {
        $colName = strtolower($col->COLUMN_NAME);
        if (!in_array($colName, ['id', 'created_at', 'updated_at'])) {
            if (!isset($allColumns[$colName])) {
                $allColumns[$colName] = [];
            }
            $allColumns[$colName][] = $tableName;
        }
    }
}

echo "Duplicate Column Names Across Tables:\n";
$hasDuplicates = false;
foreach ($allColumns as $colName => $tables) {
    if (count($tables) > 1) {
        echo "  '$colName' found in: " . implode(', ', $tables) . "\n";
        $hasDuplicates = true;
    }
}
if (!$hasDuplicates) {
    echo "  (No significant duplicates found)\n";
}

echo "\n\nLarge/Complex Tables (analysis)\n";
echo str_repeat("-", 100) . "\n";
foreach ($coreTablesInfo as $tableName => $info) {
    if ($info['column_count'] > 10) {
        echo "$tableName: " . $info['column_count'] . " columns\n";
        $fkCount = count($info['fks']);
        echo "  - Foreign Keys: $fkCount\n";
        echo "  - Records: " . $info['count'] . "\n";
    }
}
