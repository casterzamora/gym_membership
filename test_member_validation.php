<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Member;
use Carbon\Carbon;

// Get a member
$member = Member::find(1);
if (!$member) {
    echo "Member 1 not found\n";
    exit;
}

echo "=== MEMBER UPDATE - DIRECT UPDATE TEST ===\n";
echo "Current data:\n";
echo "- Name: {$member->first_name} {$member->last_name}\n";
echo "- Start: {$member->membership_start}\n";
echo "- End: {$member->membership_end}\n\n";

// Test 1: Update with valid dates
echo "Test 1: Valid date update\n";
try {
    $updated = $member->update([
        'first_name' => 'TestUpdate1',
        'membership_start' => '2026-03-01',
        'membership_end' => '2026-04-30',
    ]);
    $member->refresh();
    echo "✓ Success\n";
    echo "  - Name: {$member->first_name}\n";
    echo "  - Start: {$member->membership_start}\n";
    echo "  - End: {$member->membership_end}\n\n";
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n\n";
}

// Test 2: Update with null dates
echo "Test 2: Null date update\n";
try {
    $updated = $member->update([
        'first_name' => 'TestUpdate2',
        'membership_start' => null,
        'membership_end' => null,
    ]);
    $member->refresh();
    echo "✓ Success\n";
    echo "  - Name: {$member->first_name}\n";
    echo "  - Start: {$member->membership_start}\n";
    echo "  - End: {$member->membership_end}\n\n";
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n\n";
}

// Test 3: Check what validation rules reject
echo "Test 3: Validation check\n";
$validator = \Illuminate\Support\Facades\Validator::make([
    'first_name' => 'Test',
    'last_name' => 'User',
    'phone' => '555-1234',
    'date_of_birth' => '1990-01-15',
    'plan_id' => 1,
    'fitness_goal' => 'Test',
    'health_notes' => 'Test',
    'membership_status' => 'active',
    'membership_start' => '2026-03-01',
    'membership_end' => '2026-04-30',
], [
    'first_name' => 'sometimes|string|max:255',
    'last_name' => 'sometimes|string|max:255',
    'phone' => 'nullable|string|max:20',
    'date_of_birth' => 'nullable|date|before:today',
    'plan_id' => 'sometimes|exists:membership_plans,id',
    'fitness_goal' => 'nullable|string|max:255',
    'health_notes' => 'nullable|string',
    'membership_status' => 'nullable|string|in:active,suspended,cancelled',
    'membership_start' => 'nullable|date',
    'membership_end' => 'nullable|date|after:membership_start',
]);

if ($validator->fails()) {
    echo "✗ Validation failed:\n";
    foreach ($validator->errors()->all() as $error) {
        echo "  - $error\n";
    }
} else {
    echo "✓ Validation passed\n";
}
