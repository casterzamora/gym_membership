<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "==== EQUIPMENT CHECK ====\n\n";

$count = \App\Models\Equipment::count();
echo "Total equipment: $count\n\n";

// Try to create equipment
echo "TEST: Creating new equipment\n";
$equipmentData = [
    'equipment_name' => 'Test Equipment ' . time(),
    'status' => 'Available',
    'acquisition_date' => now()->subDays(30),
];

try {
    $equipment = \App\Models\Equipment::create($equipmentData);
    echo "✓ Equipment created: #" . $equipment->id . "\n";
    echo "Created: " . json_encode($equipment->toArray(), JSON_PRETTY_PRINT) . "\n";
} catch (\Exception $e) {
    echo "✗ Error creating equipment: " . $e->getMessage() . "\n";
}

// List all equipment
echo "\n\nAll Equipment:\n";
$equipment = \App\Models\Equipment::all();
echo "Count: " . $equipment->count() . "\n";
if ($equipment->count() > 0) {
    echo json_encode($equipment->toArray(), JSON_PRETTY_PRINT);
}
