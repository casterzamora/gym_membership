<?php
// Simulate the update request
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create(
    '/api/v1/schedules/31',
    'PUT',
    [],
    [],
    [],
    ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
    json_encode([
        'class_id' => 20,
        'class_date' => '2026-04-29',
        'start_time' => '08:00',
        'end_time' => '09:00',
        'recurrence_type' => 'weekly',
        'recurrence_end_date' => '2026-05-06'
    ])
);

// Set auth header (simulate logged-in trainer)
$request->headers->set('Authorization', 'Bearer test-token');

echo "=== Testing Schedule Update ===\n";
echo "Request URL: " . $request->getPathInfo() . "\n";
echo "Request Method: " . $request->getMethod() . "\n";
echo "Request Data: " . json_encode($request->all(), JSON_PRETTY_PRINT) . "\n\n";

try {
    $response = $kernel->handle($request);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Content:\n";
    $content = json_decode($response->getContent(), true);
    echo json_encode($content, JSON_PRETTY_PRINT) . "\n";
    
    $kernel->terminate($request, $response);
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "Trace:\n";
    echo $e->getTraceAsString();
}
