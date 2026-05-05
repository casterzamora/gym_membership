<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=gym_membership', 'root', '');

echo "==== MEMBERS TABLE COLUMNS ====\n";
$stmt = $pdo->query('DESCRIBE members');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\n==== TRAINERS TABLE COLUMNS ====\n";
$stmt = $pdo->query('DESCRIBE trainers');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\n==== USERS TABLE COLUMNS ====\n";
$stmt = $pdo->query('DESCRIBE users');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
