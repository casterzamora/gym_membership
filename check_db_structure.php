<?php

declare(strict_types=1);

function envValue(string $key, ?string $default = null): ?string
{
    static $env = null;

    if ($env === null) {
        $env = parse_ini_file(__DIR__ . DIRECTORY_SEPARATOR . '.env', false, INI_SCANNER_RAW) ?: [];
    }

    $value = $env[$key] ?? $default;
    if (!is_string($value)) {
        return $default;
    }

    return trim($value, " \t\n\r\0\x0B\"");
}

$host = envValue('DB_HOST', '127.0.0.1');
$port = envValue('DB_PORT', '3306');
$database = envValue('DB_DATABASE', 'gym');
$username = envValue('DB_USERNAME', 'root');
$password = envValue('DB_PASSWORD', '');

$mysqli = @new mysqli($host, $username, $password, $database, (int) $port);

if ($mysqli->connect_error) {
    fwrite(STDERR, 'Connection failed: ' . $mysqli->connect_error . PHP_EOL);
    exit(1);
}

$requiredTables = [
    'members',
    'trainers',
    'users',
    'membership_plans',
    'fitness_classes',
    'class_schedules',
    'attendance',
    'payments',
    'payment_methods',
    'equipment',
    'class_equipment',
    'equipment_usage',
    'trainer_certifications',
    'certifications',
    'membership_upgrades',
];

echo 'Database: ' . $database . PHP_EOL;

$tables = [];
$tableResult = $mysqli->query("SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = '{$database}' ORDER BY TABLE_NAME");
while ($row = $tableResult->fetch_assoc()) {
    $tables[] = $row['TABLE_NAME'];
}

echo 'Total tables: ' . count($tables) . PHP_EOL . PHP_EOL;
echo 'Table presence check:' . PHP_EOL;

$missing = [];
foreach ($requiredTables as $table) {
    if (in_array($table, $tables, true)) {
        echo '[OK] ' . $table . PHP_EOL;
        continue;
    }

    $missing[] = $table;
    echo '[MISSING] ' . $table . PHP_EOL;
}

echo PHP_EOL . 'Core record counts:' . PHP_EOL;
foreach (['members', 'trainers', 'users', 'attendance', 'payments'] as $table) {
    $countResult = $mysqli->query("SELECT COUNT(*) AS c FROM `{$table}`");
    if (!$countResult) {
        echo '- ' . $table . ': not available' . PHP_EOL;
        continue;
    }

    $count = (int) $countResult->fetch_assoc()['c'];
    echo '- ' . $table . ': ' . $count . PHP_EOL;
}

if ($missing !== []) {
    echo PHP_EOL . 'Result: FAIL (missing required tables)' . PHP_EOL;
    $mysqli->close();
    exit(2);
}

echo PHP_EOL . 'Result: PASS' . PHP_EOL;
$mysqli->close();
