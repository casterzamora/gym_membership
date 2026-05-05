<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "==== EQUIPMENT API TEST ====\n\n";

// Test list endpoint response format
$equipment = \App\Models\Equipment::all();

echo "Equipment list response (as API would return):\n";
$response = [
    'success' => true,
    'message' => 'Equipment listed successfully',
    'data' => $equipment,
];
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

echo "\n\n==== FRONTEND PARSING TEST ====\n";
echo "response.data = " . (count($response['data']) > 0 ? "✓ Has data" : "✗ No data") . "\n";
echo "Number of equipment: " . count($response['data']) . "\n";
echo "First equipment: " . json_encode($response['data'][0] ?? null) . "\n";
