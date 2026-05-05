<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'gym_membership');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "=== UPGRADE DEBUGGING ===\n\n";

// Check membership_upgrades table
echo "1. Checking membership_upgrades table:\n";
$result = $conn->query("SELECT * FROM membership_upgrades ORDER BY id DESC LIMIT 5");
echo "   Records found: " . $result->num_rows . "\n";
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "   - ID: {$row['id']}, Member: {$row['member_id']}, Old: {$row['old_plan_id']}, New: {$row['new_plan_id']}\n";
    }
} else {
    echo "   No upgrade records yet\n";
}

// Check members table for plan_id issues
echo "\n2. Checking members with plan_id values:\n";
$result = $conn->query("SELECT id, first_name, last_name, plan_id, membership_end FROM members LIMIT 5");
while($row = $result->fetch_assoc()) {
    echo "   - ID: {$row['id']}, Name: {$row['first_name']} {$row['last_name']}, Plan: {$row['plan_id']}, End: {$row['membership_end']}\n";
}

// Check membership_plans
echo "\n3. Checking membership_plans:\n";
$result = $conn->query("SELECT id, plan_name FROM membership_plans");
while($row = $result->fetch_assoc()) {
    echo "   - ID: {$row['id']}, Name: {$row['plan_name']}\n";
}

// Check for foreign key constraints
echo "\n4. Checking foreign key constraints on membership_upgrades:\n";
$result = $conn->query("
    SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_NAME = 'membership_upgrades'
");
while($row = $result->fetch_assoc()) {
    echo "   - {$row['CONSTRAINT_NAME']}: {$row['COLUMN_NAME']} -> {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}\n";
}

$conn->close();
?>
