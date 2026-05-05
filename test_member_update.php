<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

// Get an admin user
$admin = User::where('role', 'admin')->first();
if (!$admin) {
    echo "No admin user found\n";
    exit;
}

// Get a member
$member = Member::find(1);
if (!$member) {
    echo "Member 1 not found\n";
    exit;
}

echo "=== MEMBER UPDATE TEST ===\n";
echo "Current Member Data:\n";
echo "- ID: {$member->id}\n";
echo "- Name: {$member->first_name} {$member->last_name}\n";
echo "- Phone: {$member->phone}\n";
echo "- DOB: {$member->date_of_birth}\n";
echo "- Plan: {$member->plan_id}\n";
echo "- Start: {$member->membership_start}\n";
echo "- End: {$member->membership_end}\n";
echo "- Status: {$member->membership_status}\n\n";

// Create a mock request with valid update data
$updateData = [
    'first_name' => 'UpdatedName',
    'last_name' => 'UpdatedLast',
    'phone' => '555-1111',
    'date_of_birth' => '1990-01-15',
    'plan_id' => 1,
    'fitness_goal' => 'Updated Goal',
    'health_notes' => 'Updated Notes',
    'membership_status' => 'active',
    'membership_start' => '2026-03-01',
    'membership_end' => '2026-04-30',
];

$request = Request::create(
    "/api/v1/members/{$member->id}",
    'PUT',
    $updateData
);
$request->setUserResolver(function () use ($admin) {
    return $admin;
});

// Create the controller
$controller = new \App\Http\Controllers\Api\MemberController();

echo "Attempting update with:\n";
echo json_encode($updateData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

try {
    $response = $controller->update($request, $member);
    
    echo "Response Status: {$response->status()}\n";
    echo "Response Data:\n";
    $data = $response->getData();
    echo "  Success: " . ($data->success ? 'true' : 'false') . "\n";
    echo "  Message: {$data->message}\n";
    
    if (isset($data->data)) {
        echo "  Member Updated: {$data->data->first_name}\n";
    }
    
    if (isset($data->errors)) {
        echo "  Errors:\n";
        foreach ($data->errors as $field => $msgs) {
            echo "    - $field: " . implode(', ', $msgs) . "\n";
        }
    }
} catch (\Exception $e) {
    echo "Exception: {$e->getMessage()}\n";
    echo "Trace:\n{$e->getTraceAsString()}\n";
}
