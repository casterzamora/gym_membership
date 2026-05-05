<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "==== EQUIPMENT LIST ENDPOINT TEST ====\n\n";

// Simulate what the API returns
$equipment = \App\Models\Equipment::all();

// This is what the controller returns
$response = [
    'success' => true,
    'message' => 'Equipment retrieved successfully',
    'data' => $equipment,
];

echo "API Response structure:\n";
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) | head -50;

echo "\n\n==== FRONTEND PARSING ====\n";
echo "response.data?.data exists: " . (isset($response['data'][0]) ? "YES" : "NO") . "\n";
echo "response.data is array: " . (is_array($response['data']) ? "YES" : "NO") . "\n";
echo "Number of items: " . count($response['data']) . "\n";

// This is what the frontend does
$equipmentList = $response['data'] ?? $response;  // Will be the array
echo "\nWhat frontend gets: " . count($equipmentList) . " items\n";
