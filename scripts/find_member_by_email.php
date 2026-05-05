<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\\Contracts\\Console\\Kernel');
$kernel->bootstrap();

use App\Models\Member;

$email = $argv[1] ?? null;
if (!$email) {
    echo json_encode(['error' => 'no_email_provided']);
    exit(1);
}

$members = Member::with('user', 'plan', 'attendances.schedule.fitnessClass')
    ->whereHas('user', function ($q) use ($email) {
        $q->where('email', $email);
    })
    ->orderBy('id')
    ->get();

echo json_encode($members->toArray(), JSON_PRETTY_PRINT);
