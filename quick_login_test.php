<?php
// Simple test to hit the login endpoint

$url = 'http://localhost:8000/api/login';
$data = json_encode([
    'email' => 'admin@gym.com',
    'password' => 'password'
]);

$options = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $data,
        'timeout' => 10
    ]
];

try {
    echo "Testing login endpoint...\n";
    echo "URL: $url\n";
    echo "Data: $data\n\n";
    
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        $error = error_get_last();
        echo "Request failed: " . $error['message'] . "\n";
        echo "HTTP headers: " . var_export($http_response_header ?? [], true) . "\n";
    } else {
        echo "Response received:\n";
        echo json_encode(json_decode($response, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
