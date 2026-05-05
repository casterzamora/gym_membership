<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\\Contracts\\Console\\Kernel');
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Models\Trainer;
use App\Http\Controllers\Api\MemberController;

$trainerId = (int)($argv[1] ?? 0);
$trainer = Trainer::find($trainerId);
if (!$trainer || !$trainer->user) {
    echo json_encode(['error' => 'trainer_not_found']);
    exit(1);
}

$request = Request::create('/api/v1/members', 'GET');
$request->setUserResolver(function () use ($trainer) {
    return $trainer->user;
});

$controller = new MemberController();
$response = $controller->index($request);

if (method_exists($response, 'getContent')) {
    echo $response->getContent();
} else {
    echo json_encode(['result' => (string)$response]);
}
