<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\\Contracts\\Console\\Kernel');
$kernel->bootstrap();

use App\Models\ClassSchedule;

$schedules = ClassSchedule::with(['fitnessClass.trainer'])->orderBy('start_time')->limit(50)->get();
$out = [];
foreach ($schedules as $s) {
    $out[] = [
        'id' => $s->id,
        'class_id' => $s->class_id,
        'class_name' => $s->fitnessClass?->name ?? null,
        'trainer_id' => $s->fitnessClass?->trainer?->id ?? null,
        'trainer_name' => $s->fitnessClass?->trainer?->name ?? null,
        'start_time' => isset($s->start_time) ? (string)$s->start_time : null,
        'end_time' => isset($s->end_time) ? (string)$s->end_time : null,
    ];
}
echo json_encode($out, JSON_PRETTY_PRINT);
