<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\\Contracts\\Console\\Kernel');
$kernel->bootstrap();

use App\Models\User;

$email = $argv[1] ?? null;
if (!$email) {
    echo json_encode(['error' => 'no_email_provided']);
    exit(1);
}

$user = User::where('email', $email)->with('member')->first();
if (!$user) {
    echo json_encode(['not_found' => true]);
    exit(0);
}

echo json_encode($user->toArray());
