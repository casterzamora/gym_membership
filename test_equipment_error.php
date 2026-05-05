<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "==== EQUIPMENT CREATION DEBUG ====\n\n";

// Simulate what frontend is sending
$formData = [
    'equipment_name' => 'Test Weights ' . time(),
    'status' => 'Available',
];

echo "Form Data:\n";
echo json_encode($formData, JSON_PRETTY_PRINT) . "\n\n";

// Test 1: Validate using StoreEquipmentRequest rules
echo "TEST 1: Validation\n";
$validator = \Illuminate\Support\Facades\Validator::make($formData, [
    'equipment_name' => 'required|string|max:255|unique:equipment',
    'status' => 'nullable|in:Available,Maintenance,Out of Service',
    'acquisition_date' => 'nullable|date',
    'last_maintenance' => 'nullable|date',
]);

if ($validator->fails()) {
    echo "✗ Validation failed:\n";
    foreach ($validator->errors()->all() as $error) {
        echo "  - $error\n";
    }
    exit(1);
}

echo "✓ Validation passed\n\n";

// Test 2: Try to create
echo "TEST 2: Equipment Creation\n";
try {
    $validated = $validator->validated();
    echo "Validated data:\n";
    echo json_encode($validated, JSON_PRETTY_PRINT) . "\n\n";
    
    $equipment = \App\Models\Equipment::create($validated);
    echo "✓ Equipment created: ID #" . $equipment->id . "\n";
    
    // Format response (without loading missing relationships)
    $response = [
        'success' => true,
        'message' => 'Equipment created successfully',
        'data' => $equipment,
    ];
    
    echo "\nAPI Response:\n";
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
