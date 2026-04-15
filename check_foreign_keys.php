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

$expected = [
    'attendance.member_id -> members.id',
    'attendance.schedule_id -> class_schedules.id',
    'class_equipment.class_id -> fitness_classes.id',
    'class_equipment.equipment_id -> equipment.id',
    'class_schedules.class_id -> fitness_classes.id',
    'equipment_usage.equipment_id -> equipment.id',
    'equipment_usage.schedule_id -> class_schedules.id',
    'fitness_classes.trainer_id -> trainers.id',
    'members.plan_id -> membership_plans.id',
    'membership_upgrades.member_id -> members.id',
    'membership_upgrades.old_plan_id -> membership_plans.id',
    'membership_upgrades.new_plan_id -> membership_plans.id',
    'payments.member_id -> members.id',
    'payments.payment_method_id -> payment_methods.payment_method_id',
    'trainer_certifications.trainer_id -> trainers.id',
    'trainer_certifications.certification_id -> certifications.id',
];

$sql = "
    SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = '{$database}'
      AND REFERENCED_TABLE_NAME IS NOT NULL
    ORDER BY TABLE_NAME, COLUMN_NAME
";

$result = $mysqli->query($sql);
$actual = [];
while ($row = $result->fetch_assoc()) {
    $actual[] = $row['TABLE_NAME'] . '.' . $row['COLUMN_NAME']
        . ' -> ' . $row['REFERENCED_TABLE_NAME'] . '.' . $row['REFERENCED_COLUMN_NAME'];
}

echo 'Database: ' . $database . PHP_EOL;
echo 'Expected foreign keys: ' . count($expected) . PHP_EOL;
echo 'Actual foreign keys: ' . count($actual) . PHP_EOL . PHP_EOL;

$missing = array_values(array_diff($expected, $actual));
$extra = array_values(array_diff($actual, $expected));

if ($missing !== []) {
    echo 'Missing expected foreign keys:' . PHP_EOL;
    foreach ($missing as $item) {
        echo '- ' . $item . PHP_EOL;
    }
    echo PHP_EOL;
}

if ($extra !== []) {
    echo 'Additional foreign keys not in ERD baseline:' . PHP_EOL;
    foreach ($extra as $item) {
        echo '- ' . $item . PHP_EOL;
    }
    echo PHP_EOL;
}

if ($missing === []) {
    echo 'Result: PASS' . PHP_EOL;
    $mysqli->close();
    exit(0);
}

echo 'Result: FAIL' . PHP_EOL;
$mysqli->close();
exit(2);
