<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

use App\Models\ClassSchedule;

$schedule = ClassSchedule::with('fitnessClass.trainer', 'attendances')->limit(1)->first();

echo "API Response Format:\n";
echo json_encode($schedule, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
