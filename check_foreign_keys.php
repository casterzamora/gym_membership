<?php
require 'bootstrap/app.php';
$app = app();
$db = $app['db'];

echo "=== Checking Foreign Key Constraints ===\n\n";

echo "Fitness Classes Foreign Keys:\n";
$result = $db->select("SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME, DELETE_RULE FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE k INNER JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS r ON k.CONSTRAINT_NAME = r.CONSTRAINT_NAME WHERE k.TABLE_NAME = 'fitness_classes'");
foreach($result as $row) {
    echo "  Constraint: {$row->CONSTRAINT_NAME}\n";
    echo "  Column: {$row->COLUMN_NAME} -> {$row->REFERENCED_TABLE_NAME}({$row->REFERENCED_COLUMN_NAME})\n";
    echo "  Delete Rule: {$row->DELETE_RULE}\n\n";
}

echo "Trainer Certifications Foreign Keys:\n";
$result2 = $db->select("SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME, DELETE_RULE FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE k INNER JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS r ON k.CONSTRAINT_NAME = r.CONSTRAINT_NAME WHERE k.TABLE_NAME = 'trainer_certifications'");
foreach($result2 as $row) {
    echo "  Constraint: {$row->CONSTRAINT_NAME}\n";
    echo "  Column: {$row->COLUMN_NAME} -> {$row->REFERENCED_TABLE_NAME}({$row->REFERENCED_COLUMN_NAME})\n";
    echo "  Delete Rule: {$row->DELETE_RULE}\n\n";
}

echo "=== Checking Existing Trainers ===\n";
$trainers = $db->select('SELECT id, first_name, last_name FROM trainers LIMIT 5');
foreach($trainers as $trainer) {
    echo "ID: {$trainer->id} - {$trainer->first_name} {$trainer->last_name}\n";
}
