<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\\Contracts\\Console\\Kernel');
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Models\Trainer;
use App\Models\Member;
use App\Models\User;
use App\Http\Controllers\Api\AttendanceController;

$memberId = (int)($argv[1] ?? 0);
$trainerId = (int)($argv[2] ?? 0);

$trainer = Trainer::find($trainerId);
if (!$trainer || !$trainer->user) {
    echo json_encode(['error' => 'trainer_not_found']);
    exit(1);
}

$member = Member::find($memberId);
if (!$member) {
    echo json_encode(['error' => 'member_not_found']);
    exit(1);
}

$request = Request::create('/api/v1/attendance/unenroll/'.$memberId, 'DELETE');
$request->setUserResolver(function () use ($trainer) {
    return $trainer->user;
});

$controller = new AttendanceController();
$response = $controller->unenrollMember($request, $member);

if (method_exists($response, 'getContent')) {
    echo $response->getContent();
} else {
    echo json_encode(['result' => (string) $response]);
}
