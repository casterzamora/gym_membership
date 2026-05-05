<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\\Contracts\\Console\\Kernel');
$kernel->bootstrap();

use App\Http\Requests\StoreAttendanceRequest;
use App\Models\Trainer;
use App\Http\Controllers\Api\AttendanceController;

$memberId = (int)($argv[1] ?? 0);
$scheduleId = (int)($argv[2] ?? 0);
$status = $argv[3] ?? 'Absent';
$trainerId = (int)($argv[4] ?? 0);

$trainer = Trainer::find($trainerId);
if (!$trainer || !$trainer->user) {
    echo json_encode(['error' => 'trainer_not_found']);
    exit(1);
}

/** @var StoreAttendanceRequest $request */
$request = StoreAttendanceRequest::create('/api/v1/attendance', 'POST', [
    'member_id' => $memberId,
    'schedule_id' => $scheduleId,
    'attendance_status' => $status,
    'recorded_at' => now()->toISOString(),
]);
$request->setContainer($app);
$request->setRedirector($app->make('redirect'));
$request->setUserResolver(function () use ($trainer) {
    return $trainer->user;
});
$request->validateResolved();

$controller = new AttendanceController();
$response = $controller->store($request);

if (method_exists($response, 'getContent')) {
    echo $response->getContent();
} else {
    echo json_encode(['result' => (string) $response]);
}
