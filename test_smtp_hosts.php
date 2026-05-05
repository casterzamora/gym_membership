<?php
echo "Testing SMTP connections...\n\n";

$hosts = [
    'smtp.mailjet.com' => 587,
    'api.mailjet.com' => 587,
];

foreach ($hosts as $host => $port) {
    echo "Trying $host:$port... ";
    $sock = @fsockopen($host, $port, $errno, $errstr, 5);
    if ($sock) {
        echo "✓ Connected\n";
        fclose($sock);
    } else {
        echo "✗ Failed: $errstr\n";
    }
}
