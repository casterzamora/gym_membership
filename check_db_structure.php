<?php
try {
    $mysqli = new mysqli("127.0.0.1", "root", "", "gym_membership");
    
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    
    // 1. Count total tables
    $tablesResult = $mysqli->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'gym_membership'");
    $tablesCount = $tablesResult->fetch_assoc()['count'];
    echo "=== TOTAL TABLES: $tablesCount ===\n\n";
    
    // 2. List all table names
    $tableNamesResult = $mysqli->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'gym_membership' ORDER BY table_name");
    echo "=== ALL TABLES ===\n";
    $tables = [];
    while ($row = $tableNamesResult->fetch_assoc()) {
        echo "- " . $row['table_name'] . "\n";
        $tables[] = $row['table_name'];
    }
    echo "\n";
    
    // 3. Users table structure
    echo "=== USERS TABLE STRUCTURE ===\n";
    $usersResult = $mysqli->query("DESCRIBE users");
    while ($row = $usersResult->fetch_assoc()) {
        echo sprintf("%-20s %-20s %-15s %s\n", $row['Field'], $row['Type'], ($row['Null'] === 'YES' ? 'NULL' : 'NOT NULL'), ($row['Key'] ? "KEY: " . $row['Key'] : ""));
    }
    echo "\n";
    
    // 4. Verify key tables exist
    echo "=== KEY TABLES VERIFICATION ===\n";
    $keyTables = ['members', 'trainers', 'fitness_classes', 'attendance', 'payments', 'equipment', 'membership_plans', 'schedules'];
    foreach ($keyTables as $table) {
        $exists = in_array($table, $tables) ? "✓ EXISTS" : "✗ MISSING";
        echo "- $table: $exists\n";
    }
    echo "\n";
    
    // 5. Record counts
    echo "=== RECORD COUNTS ===\n";
    $countTables = ['users', 'members', 'trainers', 'fitness_classes', 'attendance', 'payments'];
    foreach ($countTables as $table) {
        if (in_array($table, $tables)) {
            $result = $mysqli->query("SELECT COUNT(*) as count FROM $table");
            $count = $result->fetch_assoc()['count'];
            echo "- $table: $count records\n";
        } else {
            echo "- $table: TABLE NOT FOUND\n";
        }
    }
    
    $mysqli->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
