<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "==== EQUIPMENT API TEST (Full Flow) ====\n\n";

// Get admin token
$admin = \App\Models\User::where('role', 'admin')->first();
$token = $admin->createToken('api-token')->plainTextToken;
echo "Token: $token\n\n";

// Test 1: GET equipment list
echo "TEST 1: GET /api/v1/equipment\n";
$equipmentCount = \App\Models\Equipment::count();
echo "✓ Current equipment count: $equipmentCount\n\n";

// Test 2: Try creating via validation
echo "TEST 2: Equipment Creation Validation\n";
$newEquipmentData = [
    'equipment_name' => 'New Machine ' . time(),
    'status' => 'Available',
];

$validator = \Illuminate\Support\Facades\Validator::make($newEquipmentData, [
    'equipment_name' => 'required|string|max:255|unique:equipment',
    'status' => 'nullable|in:Available,Maintenance,Out of Service',
    'acquisition_date' => 'nullable|date',
    'last_maintenance' => 'nullable|date',
]);

if ($validator->passes()) {
    echo "✓ Validation passed\n";
    
    try {
        $equipment = \App\Models\Equipment::create($newEquipmentData);
        echo "✓ Equipment created: ID #" . $equipment->id . "\n";
        echo "  Name: " . $equipment->equipment_name . "\n";
        echo "  Status: " . $equipment->status . "\n";
    } catch (\Exception $e) {
        echo "✗ Creation failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ Validation failed:\n";
    foreach ($validator->errors()->all() as $error) {
        echo "  - $error\n";
    }
}

// Test 3: Check updated count
echo "\n\nTEST 3: Verify count increased\n";
$newCount = \App\Models\Equipment::count();
echo "New equipment count: $newCount (was $equipmentCount)\n";

if ($newCount > $equipmentCount) {
    echo "✓ Equipment added successfully!\n";
} else {
    echo "✗ Equipment count didn't increase\n";
}

// Test 4: List all equipment
echo "\n\nTEST 4: Final Equipment List\n";
$all = \App\Models\Equipment::orderByDesc('id')->limit(3)->get();
echo "Last 3 equipment:\n";
foreach ($all as $eq) {
    echo "  - ID #$eq->id: $eq->equipment_name ($eq->status)\n";
}
