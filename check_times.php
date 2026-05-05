<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

use App\Models\ClassSchedule;

$schedules = ClassSchedule::limit(5)->get(['id', 'class_id', 'class_date', 'start_time', 'end_time', 'created_at']);

echo "Time values in database:\n";
echo str_repeat("=", 80) . "\n";
foreach ($schedules as $s) {
    echo sprintf("ID: %3d | class_id: %2d | start_time: %-10s | end_time: %-10s | date: %s\n",
        $s->id,
        $s->class_id, 
        (string)$s->start_time,
        (string)$s->end_time,
        $s->class_date
    );
}
echo str_repeat("=", 80) . "\n";
