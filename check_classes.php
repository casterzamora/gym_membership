<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "==== CLASSES CHECK ====\n\n";

$count = \App\Models\FitnessClass::count();
echo "Total fitness classes: $count\n\n";

$classes = \App\Models\FitnessClass::with('trainer')->get();

if ($classes->count() > 0) {
    echo "Classes in database:\n";
    echo json_encode($classes->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} else {
    echo "No classes found in database\n";
    echo "\nTo add test classes, run: php artisan db:seed --class=FitnessClassSeeder\n";
}
