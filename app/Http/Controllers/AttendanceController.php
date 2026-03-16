<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Member;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
        ]);

        // Check if member has active membership
        $member = Member::find($validated['member_id']);
        $activeMembership = $member->membership()
            ->where('status', 'active')
            ->where('end_date', '>=', now()->toDateString())
            ->first();

        if (!$activeMembership) {
            return response()->json(['error' => 'No active membership'], 403);
        }

        // Check if already checked in today
        $existingCheckIn = Attendance::where('member_id', $validated['member_id'])
            ->where('date', today())
            ->whereNull('check_out_time')
            ->first();

        if ($existingCheckIn) {
            return response()->json(['error' => 'Member already checked in'], 409);
        }

        $attendance = Attendance::create([
            'member_id' => $validated['member_id'],
            'check_in_time' => now(),
            'date' => today(),
        ]);

        return response()->json($attendance, 201);
    }

    public function checkOut(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
        ]);

        $attendance = Attendance::where('member_id', $validated['member_id'])
            ->where('date', today())
            ->whereNull('check_out_time')
            ->first();

        if (!$attendance) {
            return response()->json(['error' => 'No active check-in found'], 404);
        }

        $attendance->update(['check_out_time' => now()]);

        return response()->json($attendance, 200);
    }

    public function getTodayAttendance()
    {
        return Attendance::where('date', today())
            ->with('member')
            ->get();
    }

    public function getMemberAttendance(Member $member, Request $request)
    {
        $fromDate = $request->query('from_date', now()->subMonths(1)->toDateString());
        $toDate = $request->query('to_date', now()->toDateString());

        return Attendance::where('member_id', $member->id)
            ->whereBetween('date', [$fromDate, $toDate])
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getAttendanceStats(Request $request)
    {
        $fromDate = $request->query('from_date', now()->subMonths(1)->toDateString());
        $toDate = $request->query('to_date', now()->toDateString());

        return Attendance::whereBetween('date', [$fromDate, $toDate])
            ->selectRaw('member_id, COUNT(*) as total_visits, AVG(TIMESTAMPDIFF(MINUTE, check_in_time, check_out_time)) as avg_duration')
            ->groupBy('member_id')
            ->with('member')
            ->get();
    }
}
