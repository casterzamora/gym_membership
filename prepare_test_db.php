<?php

declare(strict_types=1);

function loadEnvFile(string $path): array
{
    if (!file_exists($path)) {
        return [];
    }

    return parse_ini_file($path, false, INI_SCANNER_RAW) ?: [];
}

function envValue(array $env, string $key, string $default = ''): string
{
    $value = $env[$key] ?? $default;
    if (!is_string($value)) {
        return $default;
    }

    return trim($value, " \t\n\r\0\x0B\"");
}

$env = loadEnvFile(__DIR__ . DIRECTORY_SEPARATOR . '.env.testing');
if ($env === []) {
    fwrite(STDERR, "Missing .env.testing; cannot prepare testing database." . PHP_EOL);
    exit(1);
}

$host = envValue($env, 'DB_HOST', '127.0.0.1');
$port = (int) envValue($env, 'DB_PORT', '3306');
$database = envValue($env, 'DB_DATABASE', 'gym_testing');
$username = envValue($env, 'DB_USERNAME', 'root');
$password = envValue($env, 'DB_PASSWORD', '');

$mysqli = @new mysqli($host, $username, $password, '', $port);
if ($mysqli->connect_error) {
    fwrite(STDERR, 'DB connection failed: ' . $mysqli->connect_error . PHP_EOL);
    exit(1);
}

if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    fwrite(STDERR, 'Failed creating test database: ' . $mysqli->error . PHP_EOL);
    $mysqli->close();
    exit(1);
}

echo 'Test database ready: ' . $database . PHP_EOL;
$mysqli->close();
