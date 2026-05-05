<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$trainer = \App\Models\Trainer::first();
if ($trainer) {
    echo "Trainer ID: {$trainer->id}\n";
    echo "Trainer User ID: {$trainer->user_id}\n";
    if ($trainer->user) {
        echo "Has User: YES\n";
        echo "User Email: {$trainer->user->email}\n";
    } else {
        echo "Has User: NO - Missing linked user!\n";
    }
} else {
    echo "No trainers found\n";
}
