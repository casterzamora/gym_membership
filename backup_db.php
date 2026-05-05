<?php
$timestamp = date('Y-m-d_H-i-s');
$backupFile = 'backups/gym_membership_backup_' . $timestamp . '.sql';
@mkdir('backups', 0755, true);

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=gym_membership', 'root', '');
    
    // Get all tables
    $tables = [];
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    $backup = "-- Database: gym_membership\n";
    $backup .= "-- Backup Date: " . date('Y-m-d H:i:s') . "\n";
    $backup .= "-- Total Tables: " . count($tables) . "\n\n";
    $backup .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    
    foreach ($tables as $table) {
        $backup .= "-- Table: " . $table . "\n";
        $backup .= "DROP TABLE IF EXISTS `" . $table . "`;\n";
        
        // Get CREATE TABLE statement
        $stmt = $pdo->query("SHOW CREATE TABLE `" . $table . "`");
        $createTable = $stmt->fetch(PDO::FETCH_NUM)[1];
        $backup .= $createTable . ";\n\n";
        
        // Get table data
        $stmt = $pdo->query("SELECT * FROM `" . $table . "`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            $columns = array_keys($rows[0]);
            $backup .= "INSERT INTO `" . $table . "` (" . implode(', ', array_map(fn($c) => "`" . $c . "`", $columns)) . ") VALUES\n";
            
            $values = [];
            foreach ($rows as $row) {
                $rowValues = [];
                foreach ($row as $val) {
                    $rowValues[] = $val === null ? 'NULL' : "'" . addslashes($val) . "'";
                }
                $values[] = "(" . implode(', ', $rowValues) . ")";
            }
            $backup .= implode(",\n", $values) . ";\n\n";
        }
    }
    
    $backup .= "SET FOREIGN_KEY_CHECKS=1;\n";
    
    file_put_contents($backupFile, $backup);
    $size = filesize($backupFile);
    
    echo "✓ Backup created successfully\n";
    echo "  File: " . $backupFile . "\n";
    echo "  Size: " . number_format($size) . " bytes\n";
    echo "  Tables: " . count($tables) . "\n";
    
} catch (Exception $e) {
    echo "✗ Backup failed: " . $e->getMessage() . "\n";
    exit(1);
}
