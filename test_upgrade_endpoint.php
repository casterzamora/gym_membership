<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\User;

// Get an admin
$admin = User::where('role', 'admin')->first();
$member = Member::find(3); // Member 3
$newPlan = MembershipPlan::find(2); // Try plan 2

if (!$admin || !$member || !$newPlan) {
    echo "Missing test data\n";
    exit;
}

echo "=== UPGRADE ENDPOINT TEST ===\n";
echo "Admin: {$admin->name}\n";
echo "Member: {$member->first_name} (Current Plan: {$member->plan_id})\n";
echo "New Plan: {$newPlan->plan_name} (ID: {$newPlan->id})\n\n";

// Create request
$request = new \Illuminate\Http\Request();
$request->setMethod('POST');
$request->request->add(['new_plan_id' => $newPlan->id]);
$request->setUserResolver(function () use ($admin) { return $admin; });

// Call controller
$controller = new \App\Http\Controllers\Api\MemberController();

try {
    $response = $controller->upgrade($request, $member);
    echo "Status: {$response->status()}\n";
    
    $data = $response->getData(true);
    echo "Success: " . ((isset($data['success']) && $data['success']) ? 'true' : 'false') . "\n";
    echo "Message: {$data['message']}\n";
    
    if (isset($data['data'])) {
        $member->refresh();
        echo "Member Plan After: {$member->plan_id}\n";
    }
    if (isset($data['errors'])) {
        echo "Errors: " . json_encode($data['errors']) . "\n";
    }
} catch (\Exception $e) {
    echo "Exception: {$e->getMessage()}\n";
}
