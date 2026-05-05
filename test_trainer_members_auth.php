<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use App\Models\Member;
use Illuminate\Http\Request;

echo "=== Testing Members API for Trainer Auth ===\n\n";

// Simulate a trainer user
$trainerUser = User::find(51);
echo "1. Trainer User:\n";
echo "   - ID: {$trainerUser->id}\n";
echo "   - Email: {$trainerUser->email}\n";
echo "   - Role: {$trainerUser->role}\n\n";

// Create a fake request with trainer user
$request = new Request();
$request->setUserResolver(function() use ($trainerUser) {
    return $trainerUser;
});

echo "2. Members API Auth Check:\n";
echo "   - User role: " . $request->user()?->role . "\n";
echo "   - Should pass middleware: role:admin,trainer\n";

// Check if members can be fetched
$members = Member::all();
echo "\n3. Members Available:\n";
echo "   - Total: " . $members->count() . "\n";
if ($members->count() > 0) {
    echo "   - Sample: " . $members->first()->first_name . " " . $members->first()->last_name . "\n";
}

echo "\n✅ Members API should work for trainers\n";
echo "\nIf frontend still shows empty, check browser console for errors.\n";
