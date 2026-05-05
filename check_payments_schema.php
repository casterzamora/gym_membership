<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=gym_membership', 'root', '');

echo "=== PAYMENTS TABLE SCHEMA ===\n";
$stmt = $pdo->query('DESCRIBE payments');
$columns = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
    $columns[] = $row['Field'];
}

echo "\nTotal columns: " . count($columns) . "\n";

echo "\n=== FOREIGN KEYS ===\n";
$stmt = $pdo->query("
SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_NAME = 'payments' AND REFERENCED_TABLE_NAME IS NOT NULL
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['CONSTRAINT_NAME'] . ": " . $row['COLUMN_NAME'] . " → " . $row['REFERENCED_TABLE_NAME'] . "." . $row['REFERENCED_COLUMN_NAME'] . "\n";
}

echo "\n=== PAYMENT DATA SAMPLE ===\n";
$stmt = $pdo->query('SELECT * FROM payments LIMIT 2');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "\nPayment #" . $row['id'] . ":\n";
    foreach ($row as $col => $val) {
        echo "  " . $col . ": " . ($val === null ? "NULL" : $val) . "\n";
    }
}
