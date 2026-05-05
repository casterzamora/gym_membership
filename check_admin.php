<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$admin = \App\Models\User::where('role', 'admin')->first();
echo "Admin User:\n";
echo json_encode($admin->toArray(), JSON_PRETTY_PRINT);
