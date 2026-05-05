<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Member;
use App\Models\Attendance;
use App\Models\ClassSchedule;

echo "=== MEMBER & ATTENDANCE CHECK ===\n\n";

// Get all members
$members = Member::all();
echo "Total Members: " . $members->count() . "\n";

if ($members->isEmpty()) {
    echo "⚠️  NO MEMBERS IN DATABASE\n";
} else {
    echo "\nList of Members:\n";
    foreach ($members as $m) {
        $attendanceCount = Attendance::where('member_id', $m->id)->count();
        $userData = $m->user ? "(User: {$m->user->id}, {$m->user->email})" : "(No User linked)";
        echo "  Member ID {$m->id}: {$m->first_name} {$m->last_name} {$userData} - {$attendanceCount} attendances\n";
    }
}

// Check if any members are already enrolled in class 8
echo "\n--- Current Enrollments in Class 8 (Spinningg) ---\n";
$class8Schedules = ClassSchedule::where('class_id', 8)->pluck('id')->toArray();
$enrolled = Attendance::whereIn('schedule_id', $class8Schedules)->with('member')->get();

if ($enrolled->isEmpty()) {
    echo "No one enrolled yet\n";
} else {
    foreach ($enrolled as $att) {
        echo "  Member: {$att->member->first_name} {$att->member->last_name}\n";
    }
}

// Show the exact schedules and their current enrollment
echo "\n--- Class 8 Schedules & Current Enrollment ---\n";
foreach ($class8Schedules as $schedule_id) {
    $schedule = ClassSchedule::find($schedule_id);
    $enrollmentCount = Attendance::where('schedule_id', $schedule_id)->count();
    echo "Schedule ID {$schedule_id}: {$schedule->class_date} {$schedule->start_time} - {$enrollmentCount} enrolled\n";
}
