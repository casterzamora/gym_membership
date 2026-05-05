<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use App\Models\Member;

echo "=== Testing Members API for Trainer ===\n\n";

// Get trainer user
$trainerUser = User::find(51);
echo "1. Trainer: {$trainerUser->email}\n";
echo "   Role: {$trainerUser->role}\n\n";

// Test members list
$members = Member::all();
echo "2. Members in Database:\n";
echo "   Total: " . $members->count() . "\n";

if ($members->count() > 0) {
    echo "   Sample members:\n";
    foreach ($members->take(3) as $m) {
        echo "      - {$m->first_name} {$m->last_name} ({$m->email})\n";
    }
}

echo "\n3. Member Relationships:\n";
$sample = $members->first();
if ($sample) {
    echo "   Sample Member (ID: {$sample->id}):\n";
    echo "      - plan_id: {$sample->plan_id}\n";
    echo "      - membership_status: {$sample->membership_status}\n";
    echo "      - Plan: {$sample->plan?->plan_name}\n";
    echo "      - Attendances: " . ($sample->attendances ? $sample->attendances->count() : 0) . "\n";
}

echo "\n✅ API should return all members for trainer to see\n";
