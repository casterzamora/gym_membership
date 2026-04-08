<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$db = DB::getDatabaseName();
echo "Database: $db\n\n";

echo "=== Foreign Keys Referencing Trainers ===\n";
$fks = DB::select("SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = 'trainers' AND TABLE_SCHEMA = ?", [$db]);
foreach ($fks as $fk) {
    echo "{$fk->TABLE_NAME}.{$fk->COLUMN_NAME} -> trainers (Constraint: {$fk->CONSTRAINT_NAME})\n";
}

echo "\n=== Checking Cascade Rules ===\n";
$rules = DB::select("SELECT TABLE_NAME, CONSTRAINT_NAME, DELETE_RULE FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS WHERE TABLE_NAME IN ('fitness_classes', 'trainer_certifications', 'trainers') AND CONSTRAINT_SCHEMA = ?", [$db]);
foreach ($rules as $rule) {
    echo "{$rule->TABLE_NAME}: {$rule->CONSTRAINT_NAME} - Delete Rule: {$rule->DELETE_RULE}\n";
}

echo "\n=== Trainer 1 Details ===\n";
$trainer = App\Models\Trainer::find(1);
if ($trainer) {
    echo "Name: {$trainer->first_name} {$trainer->last_name}\n";
    echo "User ID: " . ($trainer->user_id ?: 'NULL') . "\n";
    echo "Classes: {$trainer->classes()->count()}\n";
    echo "Certifications: {$trainer->certifications()->count()}\n";
    
    echo "\nAttempting delete...\n";
    try {
        $trainer->delete();
        echo "✓ Trainer deleted!\n";
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "Trainer 1 not found\n";
}
