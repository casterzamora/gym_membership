<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $attendance = Attendance::with('member', 'schedule')->paginate(15);
        return $this->paginated($attendance, 'Attendance records retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttendanceRequest $request)
    {
        try {
            $attendance = Attendance::create($request->validated());
            return $this->success($attendance->load('member', 'schedule'), 'Attendance recorded successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to record attendance: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($member_id, $schedule_id)
    {
        $attendance = Attendance::where('member_id', $member_id)
            ->where('class_schedule_id', $schedule_id)
            ->first();

        if (!$attendance) {
            return $this->notFound('Attendance record not found');
        }

        return $this->success($attendance->load('member', 'schedules'), 'Attendance record retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttendanceRequest $request, $member_id, $schedule_id)
    {
        try {
            $attendance = Attendance::where('member_id', $member_id)
                ->where('schedule_id', $schedule_id)
                ->first();

            if (!$attendance) {
                return $this->notFound('Attendance record not found');
            }

            $attendance->update($request->validated());
            return $this->success($attendance->load('member', 'schedule'), 'Attendance updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update attendance: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($member_id, $schedule_id)
    {
        try {
            $attendance = Attendance::where('member_id', $member_id)
                ->where('schedule_id', $schedule_id)
                ->first();

            if (!$attendance) {
                return $this->notFound('Attendance record not found');
            }

            $attendance->delete();
            return $this->success(null, 'Attendance record deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete attendance: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Enroll member in a class (check-in to upcoming schedule)
     */
    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'class_id' => 'required|exists:fitness_classes,id',
        ]);

        try {
            \Log::info('CheckIn attempt:', $validated);
            
            // Get the next upcoming schedule for this class
            $schedule = ClassSchedule::where('class_id', $validated['class_id'])
                ->where('class_date', '>=', now()->toDateString())
                ->orderBy('class_date', 'asc')
                ->first();

            \Log::info('Schedule found:', ['schedule_id' => $schedule?->id, 'class_date' => $schedule?->class_date]);

            if (!$schedule) {
                return $this->error('No upcoming schedule found for this class', null, 404);
            }

            // Check if already enrolled using where clause (composite key safe)
            $existing = Attendance::whereRaw('member_id = ? AND schedule_id = ?', [
                $validated['member_id'],
                $schedule->id
            ])->first();

            if ($existing) {
                \Log::info('Member already enrolled');
                return $this->success($existing->load('schedule.fitnessClass'), 'Already enrolled in this class', 200);
            }

            // Create new attendance record
            try {
                $attendance = new Attendance();
                $attendance->member_id = $validated['member_id'];
                $attendance->schedule_id = $schedule->id;
                $attendance->attendance_status = 'Present';
                $attendance->recorded_at = now();
                $attendance->save();
                
                \Log::info('Attendance created:', ['member_id' => $attendance->member_id, 'schedule_id' => $attendance->schedule_id]);

                return $this->success($attendance->load('schedule.fitnessClass'), 'Member enrolled successfully', 201);
            } catch (\Exception $createError) {
                \Log::error('Failed to create attendance:', ['error' => $createError->getMessage()]);
                throw $createError;
            }
        } catch (\Exception $e) {
            \Log::error('CheckIn error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $this->error('Failed to enroll member: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Check-out member from a class (optional, for tracking check-outs)
     */
    public function checkOut(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'schedule_id' => 'required|exists:class_schedules,id',
        ]);

        try {
            $attendance = Attendance::where('member_id', $validated['member_id'])
                ->where('schedule_id', $validated['schedule_id'])
                ->first();

            if (!$attendance) {
                return $this->error('No attendance record found', null, 404);
            }

            // Mark as marked (can be used for tracking manual check-outs)
            $attendance->update(['attendance_status' => 'Present']);
            return $this->success($attendance, 'Member check-out recorded successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to process check-out: ' . $e->getMessage(), null, 500);
        }
    }
}
