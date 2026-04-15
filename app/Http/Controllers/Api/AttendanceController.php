<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\Member;
use App\Models\Trainer;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Attendance::with('member', 'schedule.fitnessClass');

        $actor = $request->user();

        if ($actor instanceof Member) {
            $query->where('member_id', $actor->id);
        }

        if ($actor instanceof User && $actor->role === 'trainer') {
            $trainer = $this->resolveTrainerFromUser($actor);

            if (!$trainer) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereHas('schedule.fitnessClass', function ($q) use ($trainer) {
                    $q->where('trainer_id', $trainer->id);
                });
            }
        }

        $attendance = $query->paginate(15);
        return $this->paginated($attendance, 'Attendance records retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttendanceRequest $request)
    {
        try {
            $data = $request->validated();
            $actor = $request->user();

            if ($actor instanceof Member && (int) $actor->id !== (int) $data['member_id']) {
                return $this->error('Forbidden: members can only record their own attendance', null, 403);
            }

            if ($actor instanceof User && $actor->role === 'trainer') {
                $trainer = $this->resolveTrainerFromUser($actor);
                if (!$trainer) {
                    return $this->error('Forbidden: trainer profile not found', null, 403);
                }

                $isOwnedSchedule = ClassSchedule::query()
                    ->where('id', $data['schedule_id'])
                    ->whereHas('fitnessClass', function ($q) use ($trainer) {
                        $q->where('trainer_id', $trainer->id);
                    })
                    ->exists();

                if (!$isOwnedSchedule) {
                    return $this->error('Forbidden: trainers can only record attendance for their own classes', null, 403);
                }
            }

            $existing = Attendance::query()
                ->where('member_id', $data['member_id'])
                ->where('schedule_id', $data['schedule_id'])
                ->first();

            if ($existing) {
                return $this->error('Attendance already exists for this member and schedule', null, 409);
            }

            if (!isset($data['recorded_at'])) {
                $data['recorded_at'] = now();
            }

            $attendance = Attendance::create($data);
            return $this->success($attendance->load('member', 'schedule'), 'Attendance recorded successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to record attendance: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $member_id, int $schedule_id)
    {
        $scopeCheck = $this->enforceAttendanceScope($request->user(), $member_id, $schedule_id);
        if ($scopeCheck !== null) {
            return $scopeCheck;
        }

        $attendance = Attendance::where('member_id', $member_id)
            ->where('schedule_id', $schedule_id)
            ->first();

        if (!$attendance) {
            return $this->notFound('Attendance record not found');
        }

        return $this->success($attendance->load('member', 'schedule.fitnessClass'), 'Attendance record retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttendanceRequest $request, int $member_id, int $schedule_id)
    {
        try {
            $scopeCheck = $this->enforceAttendanceScope($request->user(), $member_id, $schedule_id);
            if ($scopeCheck !== null) {
                return $scopeCheck;
            }

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
    public function destroy(Request $request, int $member_id, int $schedule_id)
    {
        try {
            $scopeCheck = $this->enforceAttendanceScope($request->user(), $member_id, $schedule_id);
            if ($scopeCheck !== null) {
                return $scopeCheck;
            }

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
            $actor = $request->user();

            if ($actor instanceof Member && (int) $actor->id !== (int) $validated['member_id']) {
                return $this->error('Forbidden: members can only check in themselves', null, 403);
            }

            $member = Member::find($validated['member_id']);
            if (!$member || $member->membership_status !== 'active') {
                return $this->error('Membership is not active', null, 403);
            }

            if ($member->membership_end && $member->membership_end->lt(now()->startOfDay())) {
                return $this->error('Membership has expired', null, 403);
            }

            if ($actor instanceof User && $actor->role === 'trainer') {
                $trainer = $this->resolveTrainerFromUser($actor);
                if (!$trainer) {
                    return $this->error('Forbidden: trainer profile not found', null, 403);
                }

                $ownsClass = ClassSchedule::query()
                    ->where('class_id', $validated['class_id'])
                    ->whereHas('fitnessClass', function ($q) use ($trainer) {
                        $q->where('trainer_id', $trainer->id);
                    })
                    ->exists();

                if (!$ownsClass) {
                    return $this->error('Forbidden: trainers can only check in members to their own classes', null, 403);
                }
            }
            
            // Get the next upcoming schedule for this class
            $schedule = ClassSchedule::where('class_id', $validated['class_id'])
                ->where('class_date', '>=', now()->toDateString())
                ->orderBy('class_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->first();

            if (!$schedule) {
                return $this->error('No upcoming schedule found for this class', null, 404);
            }

            $capacity = (int) optional($schedule->fitnessClass)->max_participants;
            if ($capacity > 0) {
                $enrolledCount = Attendance::where('schedule_id', $schedule->id)->count();
                if ($enrolledCount >= $capacity) {
                    return $this->error('Class schedule is at full capacity', null, 422);
                }
            }

            // Check if already enrolled using where clause (composite key safe)
            $existing = Attendance::whereRaw('member_id = ? AND schedule_id = ?', [
                $validated['member_id'],
                $schedule->id
            ])->first();

            if ($existing) {
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

                return $this->success($attendance->load('schedule.fitnessClass'), 'Member enrolled successfully', 201);
            } catch (\Exception $createError) {
                throw $createError;
            }
        } catch (\Exception $e) {
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
            $actor = $request->user();

            if ($actor instanceof Member && (int) $actor->id !== (int) $validated['member_id']) {
                return $this->error('Forbidden: members can only check out themselves', null, 403);
            }

            if ($actor instanceof User && $actor->role === 'trainer') {
                $trainer = $this->resolveTrainerFromUser($actor);
                if (!$trainer) {
                    return $this->error('Forbidden: trainer profile not found', null, 403);
                }

                $isOwnedSchedule = ClassSchedule::query()
                    ->where('id', $validated['schedule_id'])
                    ->whereHas('fitnessClass', function ($q) use ($trainer) {
                        $q->where('trainer_id', $trainer->id);
                    })
                    ->exists();

                if (!$isOwnedSchedule) {
                    return $this->error('Forbidden: trainers can only check out members from their own classes', null, 403);
                }
            }

            $attendance = Attendance::where('member_id', $validated['member_id'])
                ->where('schedule_id', $validated['schedule_id'])
                ->first();

            if (!$attendance) {
                return $this->error('No attendance record found', null, 404);
            }

            // Mark as marked (can be used for tracking manual check-outs)
            $attendance->update([
                'attendance_status' => 'Present',
                'recorded_at' => now(),
            ]);
            return $this->success($attendance, 'Member check-out recorded successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to process check-out: ' . $e->getMessage(), null, 500);
        }
    }

    private function enforceAttendanceScope($actor, int $memberId, int $scheduleId)
    {
        if ($actor instanceof Member && (int) $actor->id !== $memberId) {
            return $this->error('Forbidden: members can only access their own attendance records', null, 403);
        }

        if ($actor instanceof User && $actor->role === 'trainer') {
            $trainer = $this->resolveTrainerFromUser($actor);

            if (!$trainer) {
                return $this->error('Forbidden: trainer profile not found', null, 403);
            }

            $isOwnedSchedule = ClassSchedule::query()
                ->where('id', $scheduleId)
                ->whereHas('fitnessClass', function ($q) use ($trainer) {
                    $q->where('trainer_id', $trainer->id);
                })
                ->exists();

            if (!$isOwnedSchedule) {
                return $this->error('Forbidden: trainers can only access attendance for their own classes', null, 403);
            }
        }

        return null;
    }

    private function resolveTrainerFromUser(User $user): ?Trainer
    {
        return Trainer::where('email', $user->email)->first();
    }
}
