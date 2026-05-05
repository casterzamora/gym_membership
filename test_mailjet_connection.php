<?php
require __DIR__ . '/vendor/autoload.php';

$host = 'smtp.mailjet.com';
$port = 587;
$username = '867628da09635489a3e3c918aceb7d8c';
$password = 'b05def254176d5350922f502894dd1ce';

echo "=== Mailjet SMTP Connection Test ===\n\n";
echo "Connecting to: $host:$port\n";
echo "Username: " . substr($username, 0, 8) . "...\n\n";

// Test SMTP connection
$connection = @fsockopen($host, $port, $errno, $errstr, 5);

if (!$connection) {
    echo "❌ Failed to connect to Mailjet SMTP server\n";
    echo "Error: $errstr (errno: $errno)\n";
    exit(1);
}

echo "✓ Connected to Mailjet SMTP server\n";

// Read the initial greeting
$greeting = fgets($connection, 1024);
echo "Server response: " . trim($greeting) . "\n\n";

// Send EHLO
fwrite($connection, "EHLO localhost\r\n");
$response = fgets($connection, 1024);
echo "EHLO response: " . trim($response) . "\n";

// Consume EHLO capabilities
while (true) {
    $line = fgets($connection, 1024);
    if (substr($line, 3, 1) !== '-') break; // Last line has space instead of dash
    echo "  Capability: " . trim($line) . "\n";
}

// Try STARTTLS
fwrite($connection, "STARTTLS\r\n");
$response = fgets($connection, 1024);
echo "\n✓ STARTTLS initiated\n";

// Enable crypto
stream_context_set_option($connection, 'ssl', 'verify_peer', false);
stream_context_set_option($connection, 'ssl', 'verify_peer_name', false);
stream_socket_enable_crypto($connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

echo "✓ TLS encrypted connection established\n";

// Send EHLO again after STARTTLS
fwrite($connection, "EHLO localhost\r\n");
$response = fgets($connection, 1024);
echo "Post-STARTTLS EHLO: " . trim($response) . "\n";

while (true) {
    $line = fgets($connection, 1024);
    if (substr($line, 3, 1) !== '-') break;
    echo "  " . trim($line) . "\n";
}

// Try AUTH LOGIN
echo "\n--- Attempting Authentication ---\n";
fwrite($connection, "AUTH LOGIN\r\n");
$response = fgets($connection, 1024);
echo "AUTH response: " . trim($response) . "\n";

if (strpos($response, '334') === 0) {
    // Send base64-encoded username
    fwrite($connection, base64_encode($username) . "\r\n");
    $response = fgets($connection, 1024);
    echo "Username auth: " . trim($response) . "\n";
    
    if (strpos($response, '334') === 0) {
        // Send base64-encoded password
        fwrite($connection, base64_encode($password) . "\r\n");
        $response = fgets($connection, 1024);
        echo "Password auth: " . trim($response) . "\n";
        
        if (strpos($response, '235') === 0) {
            echo "\n✅ Authentication successful!\n";
            echo "Your Mailjet SMTP setup is configured correctly.\n";
        } else {
            echo "\n❌ Password authentication failed\n";
        }
    }
}

// Close connection
fwrite($connection, "QUIT\r\n");
fclose($connection);
