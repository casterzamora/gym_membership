<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Member;
use App\Models\FitnessClass;
use App\Models\ClassSchedule;
use App\Http\Controllers\Api\AttendanceController;
use Illuminate\Http\Request;

try {
    echo "=== Testing Class Enrollment ===\n\n";
    
    // Get a member
    $member = Member::first();
    if (!$member) {
        echo "❌ No members found\n";
        exit(1);
    }
    
    echo "Member: {$member->email} (ID: {$member->id})\n";
    echo "  - Status: {$member->membership_status}\n";
    echo "  - Membership End: {$member->membership_end}\n\n";
    
    // Get a class
    $class = FitnessClass::first();
    if (!$class) {
        echo "❌ No fitness classes found\n";
        exit(1);
    }
    
    echo "Class: {$class->class_name} (ID: {$class->id})\n";
    
    // Check for schedules
    $schedule = ClassSchedule::where('class_id', $class->id)
        ->where('class_date', '>=', now()->toDateString())
        ->first();
    
    if (!$schedule) {
        echo "  ❌ No upcoming schedule found\n";
        echo "\n  Note: You need to create a class schedule first before members can enroll\n";
        exit(0);
    }
    
    echo "  Schedule: {$schedule->class_date} at {$schedule->start_time}\n\n";
    
    // Simulate the API call
    echo "Testing enrollment...\n";
    $controller = new AttendanceController();
    
    $request = Request::create('/api/v1/attendance/check-in', 'POST', [
        'member_id' => $member->id,
        'class_id' => $class->id,
    ]);
    
    // Create a mock user from the member
    $mockUser = new \App\Models\User();
    $mockUser->id = $member->user_id;
    $mockUser->role = 'member';
    
    $request->setUserResolver(function () use ($mockUser) {
        return $mockUser;
    });
    
    $response = $controller->checkIn($request);
    $data = json_decode($response->getContent(), true);
    
    if ($data['success'] ?? false) {
        echo "✅ Enrollment successful!\n";
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "❌ Enrollment failed\n";
        echo "  Error: " . ($data['message'] ?? 'Unknown error') . "\n";
        if (isset($data['errors'])) {
            echo "  Details: " . json_encode($data['errors']) . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
