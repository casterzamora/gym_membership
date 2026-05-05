<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FitnessClass;
use App\Models\Trainer;
use App\Models\User;

// Test data
$admin = User::where('role', 'admin')->first();
$class = FitnessClass::first();
$trainer = Trainer::first();

if (!$class) {
    echo "No fitness class found in database\n";
    exit(1);
}

echo "=== CLASSES MANAGEMENT TEST ===\n";
echo "Class ID: {$class->id}\n";
echo "Class Name: {$class->class_name}\n";
echo "Trainer ID: {$class->trainer_id}\n";
echo "Max Participants: {$class->max_participants}\n\n";

// Test 1: Update
echo "TEST 1: UPDATE CLASS\n";
echo "Attempting to update class...\n";

$updateData = [
    'class_name' => $class->class_name,
    'description' => 'Updated description - ' . now()->timestamp,
    'trainer_id' => $class->trainer_id,
    'max_participants' => $class->max_participants,
];

$request = new \Illuminate\Http\Request();
$request->setMethod('PUT');
$request->request->add($updateData);
$request->setUserResolver(function () use ($admin) { return $admin; });
$request->setRouteResolver(function () use ($class) {
    $route = new \Illuminate\Routing\Route('PUT', '/api/v1/classes/{fitnessClass}', []);
    $route->setParameter('fitnessClass', $class);
    return $route;
});

$controller = new \App\Http\Controllers\Api\FitnessClassController();

try {
    $response = $controller->update($request, $class);
    echo "Status: {$response->status()}\n";
    $data = $response->getData(true);
    echo "Success: " . ($data['success'] ? 'YES' : 'NO') . "\n";
    echo "Message: {$data['message']}\n";
    if (isset($data['errors'])) {
        echo "Errors: " . json_encode($data['errors']) . "\n";
    }
} catch (\Exception $e) {
    echo "Exception: {$e->getMessage()}\n";
    echo "Trace: {$e->getTraceAsString()}\n";
}

echo "\n";

// Test 2: Delete
echo "TEST 2: DELETE CLASS\n";
echo "Attempting to delete class...\n";

// Create a test class first
$testClass = FitnessClass::create([
    'class_name' => 'Test Class - Delete Me - ' . now()->timestamp,
    'description' => 'Test class for deletion',
    'trainer_id' => $trainer->id,
    'max_participants' => 20,
]);

echo "Created test class ID: {$testClass->id}\n";

$deleteRequest = new \Illuminate\Http\Request();
$deleteRequest->setMethod('DELETE');
$deleteRequest->setUserResolver(function () use ($admin) { return $admin; });
$deleteRequest->setRouteResolver(function () use ($testClass) {
    $route = new \Illuminate\Routing\Route('DELETE', '/api/v1/classes/{fitnessClass}', []);
    $route->setParameter('fitnessClass', $testClass);
    return $route;
});

try {
    $response = $controller->destroy($deleteRequest, $testClass);
    echo "Status: {$response->status()}\n";
    $data = $response->getData(true);
    echo "Success: " . ($data['success'] ? 'YES' : 'NO') . "\n";
    echo "Message: {$data['message']}\n";
    
    // Verify deletion
    $stillExists = FitnessClass::find($testClass->id);
    echo "Class still exists? " . ($stillExists ? 'YES (ERROR)' : 'NO (Correct)') . "\n";
} catch (\Exception $e) {
    echo "Exception: {$e->getMessage()}\n";
    echo "Trace: {$e->getTraceAsString()}\n";
}

echo "\n✓ Tests completed\n";
