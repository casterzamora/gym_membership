<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=gym_membership', 'root', '');

echo "==== PAYMENTS TABLE NORMALIZATION ANALYSIS ====\n\n";

// Get detailed column information
$columns = $pdo->query('DESCRIBE payments');
echo "Current Columns:\n";
while ($col = $columns->fetch(PDO::FETCH_ASSOC)) {
    echo "  - " . $col['Field'] . " (" . $col['Type'] . ") " . ($col['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . "\n";
}

// Check for duplicate/redundant columns
echo "\n--- Redundancy Check ---\n";
$hasPaymentMethod = $pdo->query("SELECT COUNT(*) as c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='payments' AND COLUMN_NAME='payment_method' AND TABLE_SCHEMA='gym_membership'")->fetch(PDO::FETCH_ASSOC);
$hasPaymentMethodId = $pdo->query("SELECT COUNT(*) as c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='payments' AND COLUMN_NAME='payment_method_id' AND TABLE_SCHEMA='gym_membership'")->fetch(PDO::FETCH_ASSOC);

echo "  payment_method (string): " . ($hasPaymentMethod['c'] > 0 ? "YES - REDUNDANT" : "NO") . "\n";
echo "  payment_method_id (FK): " . ($hasPaymentMethodId['c'] > 0 ? "YES - CORRECT" : "NO") . "\n";

// Check for missing user_id
$hasUserId = $pdo->query("SELECT COUNT(*) as c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='payments' AND COLUMN_NAME='user_id' AND TABLE_SCHEMA='gym_membership'")->fetch(PDO::FETCH_ASSOC);
echo "  user_id (FK): " . ($hasUserId['c'] > 0 ? "YES" : "NO - MISSING") . "\n";

// Check if bookings table exists
$bookingsExists = $pdo->query("SELECT COUNT(*) as c FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='gym_membership' AND TABLE_NAME='bookings'")->fetch(PDO::FETCH_ASSOC);
echo "  bookings table: " . ($bookingsExists['c'] > 0 ? "EXISTS" : "MISSING - booking_id FKs ORPHANED") . "\n";

// Check for problematic records
echo "\n--- Data Quality ---\n";
$records = $pdo->query("SELECT COUNT(*) as c FROM payments")->fetch(PDO::FETCH_ASSOC);
echo "  Total payments: " . $records['c'] . "\n";

// Check FK constraints
echo "\n--- Foreign Keys ---\n";
$fks = $pdo->query("
    SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_NAME='payments' AND TABLE_SCHEMA='gym_membership' AND REFERENCED_TABLE_SCHEMA='gym_membership'
");
while ($fk = $fks->fetch(PDO::FETCH_ASSOC)) {
    echo "  " . $fk['COLUMN_NAME'] . " -> " . $fk['REFERENCED_TABLE_NAME'] . "\n";
}

echo "\n==== SUMMARY ====\n";
echo "Recommended Actions:\n";
if ($hasPaymentMethod['c'] > 0) {
    echo "  1. Remove payment_method (string) column\n";
}
if ($hasUserId['c'] == 0) {
    echo "  2. Add user_id FK and populate from members\n";
}
if ($bookingsExists['c'] == 0) {
    echo "  3. Remove orphaned booking_id column\n";
}
