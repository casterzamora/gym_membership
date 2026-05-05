<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use App\Models\Member;
use App\Http\Controllers\Api\MemberController;

echo "=== Testing Members API for Trainer ===\n\n";

$trainer = User::find(51);
echo "Trainer: {$trainer->email}\n";
echo "Trainer ID: {$trainer->trainer->id}\n\n";

// Simulate trainer member resolution
$memberController = new MemberController();

// Use reflection to call private method
$reflection = new \ReflectionClass($memberController);
$method = $reflection->getMethod('resolveTrainerMemberIds');
$method->setAccessible(true);
$memberIds = $method->invoke($memberController, $trainer);

echo "Members IDs returned: " . count($memberIds) . "\n";

if (count($memberIds) > 0) {
    echo "Sample member IDs: " . implode(', ', array_slice($memberIds, 0, 5)) . "\n\n";
    
    // Get actual member data
    $members = Member::whereIn('id', $memberIds)->limit(5)->get();
    echo "Sample Members:\n";
    foreach ($members as $m) {
        echo "  - {$m->first_name} {$m->last_name}\n";
    }
} else {
    echo "❌ No members returned\n";
}
