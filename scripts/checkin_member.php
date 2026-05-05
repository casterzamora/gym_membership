<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\\Contracts\\Console\\Kernel');
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Models\Trainer;
use App\Models\User;
use App\Models\Member;
use App\Models\ClassSchedule;
use App\Http\Controllers\Api\AttendanceController;

$memberId = $argv[1] ?? null;
$scheduleId = $argv[2] ?? null;
$trainerId = $argv[3] ?? null;

if (!$memberId || !$scheduleId || !$trainerId) {
    echo json_encode(['error' => 'usage: php scripts/checkin_member.php MEMBER_ID SCHEDULE_ID TRAINER_ID']);
    exit(1);
}

$trainer = Trainer::find($trainerId);
if (!$trainer) {
    echo json_encode(['error' => 'trainer_not_found']);
    exit(1);
}

$trainerUser = $trainer->user;
if (!$trainerUser) {
    echo json_encode(['error' => 'trainer_user_not_found']);
    exit(1);
}

$request = Request::create('/api/v1/attendance/check-in', 'POST');
$request->merge([
    'member_id' => (int)$memberId,
    'schedule_id' => (int)$scheduleId,
]);
$request->setUserResolver(function () use ($trainerUser) {
    return $trainerUser;
});

$controller = new AttendanceController();
$response = $controller->checkIn($request);

// If it's a JsonResponse, get content
if (method_exists($response, 'getContent')) {
    echo $response->getContent();
} elseif (is_array($response)) {
    echo json_encode($response);
} else {
    echo json_encode(['result' => (string)$response]);
}
