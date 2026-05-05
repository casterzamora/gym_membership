<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Trainer;
use App\Models\User;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $actor = $request->user();
        $scheduleId = $request->query('schedule_id');
        $classId = $request->query('class_id');
        $date = $request->query('date');

        if ($scheduleId) {
            $schedule = ClassSchedule::with('fitnessClass.trainer')->find($scheduleId);

            if (!$schedule) {
                return $this->notFound('Schedule not found');
            }

            if ($actor instanceof User && $actor->role === 'trainer') {
                $trainer = $this->resolveTrainerFromUser($actor);
                if (!$trainer) {
                    return $this->error('Forbidden: trainer profile not found', null, 403);
                }

                if ((int) optional($schedule->fitnessClass)->trainer_id !== (int) $trainer->id) {
                    return $this->error('Forbidden: trainers can only view attendance for their own classes', null, 403);
                }
            }

            $memberIdsQuery = Attendance::query()
                ->select('attendance.member_id')
                ->distinct()
                ->whereHas('schedule', function ($scheduleQuery) use ($schedule) {
                    $scheduleQuery->where('class_id', $schedule->class_id);
                });

            if ($actor instanceof User && $actor->role === 'trainer') {
                $trainer = $this->resolveTrainerFromUser($actor);
                if ($trainer) {
                    $memberIdsQuery->whereHas('schedule.fitnessClass', function ($q) use ($trainer) {
                        $q->where('trainer_id', $trainer->id);
                    });
                }
            }

            $memberIds = $memberIdsQuery->pluck('attendance.member_id')->map(fn ($id) => (int) $id)->all();

            $members = Member::with(['user', 'plan'])
                ->whereIn('id', $memberIds)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            $scheduleRecords = Attendance::with(['member.user', 'member.plan', 'schedule.fitnessClass'])
                ->where('schedule_id', $schedule->id)
                ->get();

            $recordMap = $scheduleRecords->keyBy('member_id');
            $roster = $members->map(function (Member $member) use ($recordMap, $schedule) {
                $record = $recordMap->get($member->id);

                return [
                    'member_id' => $member->id,
                    'name' => trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')),
                    'email' => $member->user?->email ?? null,
                    'phone' => $member->phone ?? $member->user?->phone ?? null,
                    'plan_name' => $member->plan?->plan_name ?? null,
                    'attendance_status' => $record?->attendance_status ?? 'Not Marked',
                    'recorded_at' => $record?->recorded_at,
                    'schedule_id' => $schedule->id,
                    'class_name' => $schedule->fitnessClass?->class_name,
                ];
            })->values();

            $summary = [
                'present' => $roster->where('attendance_status', 'Present')->count(),
                'absent' => $roster->where('attendance_status', 'Absent')->count(),
                'late' => $roster->where('attendance_status', 'Late')->count(),
                'not_marked' => $roster->where('attendance_status', 'Not Marked')->count(),
                'total' => $roster->count(),
            ];

            return $this->success([
                'schedule' => [
                    'id' => $schedule->id,
                    'class_id' => $schedule->class_id,
                    'class_name' => $schedule->fitnessClass?->class_name,
                    'class_date' => $schedule->class_date,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'trainer_name' => $schedule->fitnessClass?->trainer?->name ?? null,
                ],
                'summary' => $summary,
                'members' => $roster,
                'records' => $scheduleRecords,
            ], 'Attendance roster retrieved successfully');
        }

        $query = Attendance::with('member', 'schedule.fitnessClass');

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

        if ($classId) {
            $query->whereHas('schedule', function ($scheduleQuery) use ($classId) {
                $scheduleQuery->where('class_id', $classId);
            });
        }

        if ($date) {
            $query->whereHas('schedule', function ($scheduleQuery) use ($date) {
                $scheduleQuery->whereDate('class_date', Carbon::parse($date)->toDateString());
            });
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
            $attendance = $this->saveAttendance($request->validated(), $request->user());
            $statusCode = $attendance['created'] ? 201 : 200;

            return $this->success(
                $attendance['model']->load('member', 'schedule.fitnessClass'),
                $attendance['created'] ? 'Attendance recorded successfully' : 'Attendance updated successfully',
                $statusCode
            );
        } catch (\Exception $e) {
            return $this->error('Failed to record attendance: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Upsert an attendance record from a body-based PUT request.
     */
    public function upsert(StoreAttendanceRequest $request)
    {
        try {
            $attendance = $this->saveAttendance($request->validated(), $request->user());
            $statusCode = $attendance['created'] ? 201 : 200;

            return $this->success(
                $attendance['model']->load('member', 'schedule.fitnessClass'),
                $attendance['created'] ? 'Attendance recorded successfully' : 'Attendance updated successfully',
                $statusCode
            );
        } catch (\Exception $e) {
            return $this->error('Failed to update attendance: ' . $e->getMessage(), null, 500);
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
     * Remove a member from all attendance records tied to the trainer's classes.
     */
    public function unenrollMember(Request $request, Member $member)
    {
        try {
            $actor = $request->user();

            if ($actor instanceof Member && (int) $actor->id !== (int) $member->id) {
                return $this->error('Forbidden: members can only manage their own attendance', null, 403);
            }

            if ($actor instanceof User && $actor->role === 'trainer') {
                $trainer = $this->resolveTrainerFromUser($actor);
                if (!$trainer) {
                    return $this->error('Forbidden: trainer profile not found', null, 403);
                }

                $deleted = Attendance::query()
                    ->where('member_id', $member->id)
                    ->whereHas('schedule.fitnessClass', function ($q) use ($trainer) {
                        $q->where('trainer_id', $trainer->id);
                    })
                    ->delete();

                if ($deleted === 0) {
                    return $this->notFound('No attendance records found for this member in your classes');
                }

                return $this->success([
                    'member_id' => $member->id,
                    'deleted_attendance_count' => $deleted,
                ], 'Member unenrolled successfully');
            }

            if ($actor instanceof User && $actor->role === 'admin') {
                $deleted = Attendance::query()->where('member_id', $member->id)->delete();

                if ($deleted === 0) {
                    return $this->notFound('No attendance records found for this member');
                }

                return $this->success([
                    'member_id' => $member->id,
                    'deleted_attendance_count' => $deleted,
                ], 'Member unenrolled successfully');
            }

            return $this->error('Forbidden: only trainers and admins can unenroll members', null, 403);
        } catch (\Exception $e) {
            return $this->error('Failed to unenroll member: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Enroll member in a class (check-in to upcoming schedule)
     */
    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'class_id' => 'nullable|exists:fitness_classes,id',
            'schedule_id' => 'nullable|exists:class_schedules,id',
        ]);

        try {
            $actor = $request->user();
            $actorMemberId = null;

            if ($actor instanceof Member) {
                $actorMemberId = $actor->id;
            } elseif ($actor instanceof User && $actor->role === 'member') {
                $actorMemberId = $actor->member?->id;
            }

            if ($actorMemberId && (int) $actorMemberId !== (int) $validated['member_id']) {
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
            }

            // Determine schedule: prefer explicit schedule_id, otherwise resolve next upcoming schedule for class_id
            $schedule = null;
            if (!empty($validated['schedule_id'])) {
                $schedule = ClassSchedule::with('fitnessClass')->find($validated['schedule_id']);
                if (!$schedule) {
                    return $this->error('Schedule not found', null, 404);
                }
                // If actor is trainer, ensure they own this schedule's class
                if ($actor instanceof User && $actor->role === 'trainer') {
                    if ((int) optional($schedule->fitnessClass)->trainer_id !== (int) $trainer->id) {
                        return $this->error('Forbidden: trainers can only check in members to their own classes', null, 403);
                    }
                }
            } else {
                if (empty($validated['class_id'])) {
                    return $this->error('Either class_id or schedule_id must be provided', null, 422);
                }

                // If actor is trainer, ensure they own this class
                if ($actor instanceof User && $actor->role === 'trainer') {
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
            }

            if (!$schedule) {
                return $this->error('No upcoming schedule found for this class', null, 404);
            }

            $class = $schedule->fitnessClass()->with('membershipPlans')->first();
            if (!$class) {
                return $this->error('Fitness class not found', null, 404);
            }

            $memberPlanId = (int) ($member->plan_id ?? 0);
            if (!$memberPlanId) {
                return $this->error('Member does not have an active membership plan', null, 403);
            }

            $goldPlanId = MembershipPlan::where('plan_name', 'Gold')->value('id');
            if ($class->is_special && (int) $memberPlanId !== (int) $goldPlanId) {
                return $this->error('This special class is only available to Gold members', null, 403);
            }

            $isAllowed = $class->membershipPlans->contains(fn ($plan) => (int) $plan->id === $memberPlanId);
            if (!$isAllowed) {
                return $this->error('Your membership plan is not allowed to enroll in this class', null, 403);
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
            $actorMemberId = null;

            if ($actor instanceof Member) {
                $actorMemberId = $actor->id;
            } elseif ($actor instanceof User && $actor->role === 'member') {
                $actorMemberId = $actor->member?->id;
            }

            if ($actorMemberId && (int) $actorMemberId !== (int) $validated['member_id']) {
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
        return $user->trainer;
    }

    /**
     * Create or update an attendance row while enforcing authorization and trainer ownership.
     */
    private function saveAttendance(array $data, $actor): array
    {
        if ($actor instanceof Member && (int) $actor->id !== (int) $data['member_id']) {
            throw new \RuntimeException('Forbidden: members can only record their own attendance');
        }

        $member = Member::with('plan')->find($data['member_id']);
        if (!$member || $member->membership_status !== 'active') {
            throw new \RuntimeException('Membership is not active');
        }

        if ($member->membership_end && $member->membership_end->lt(now()->startOfDay())) {
            throw new \RuntimeException('Membership has expired');
        }

        $schedule = ClassSchedule::with('fitnessClass')->find($data['schedule_id']);
        if (!$schedule) {
            throw new \RuntimeException('Schedule not found');
        }

        if ($actor instanceof User && $actor->role === 'trainer') {
            $trainer = $this->resolveTrainerFromUser($actor);
            if (!$trainer) {
                throw new \RuntimeException('Forbidden: trainer profile not found');
            }

            if ((int) optional($schedule->fitnessClass)->trainer_id !== (int) $trainer->id) {
                throw new \RuntimeException('Forbidden: trainers can only record attendance for their own classes');
            }
        }

        $now = now();
        $recordedAt = !empty($data['recorded_at']) ? Carbon::parse($data['recorded_at']) : $now;
        $payload = [
            'attendance_status' => $data['attendance_status'],
            'attendance_notes' => $data['attendance_notes'] ?? null,
            'recorded_at' => $recordedAt,
            'updated_at' => $now,
        ];

        $existing = Attendance::query()
            ->where('member_id', $member->id)
            ->where('schedule_id', $schedule->id)
            ->first();

        $insertPayload = $payload + [
            'member_id' => $member->id,
            'schedule_id' => $schedule->id,
            'created_at' => $now,
        ];

        if ($existing) {
            Attendance::query()
                ->where('member_id', $member->id)
                ->where('schedule_id', $schedule->id)
                ->update($payload);
        } else {
            Attendance::query()->insert($insertPayload);
        }

        $attendance = Attendance::query()->where('member_id', $member->id)->where('schedule_id', $schedule->id)->first();

        return [
            'model' => $attendance,
            'created' => !$existing,
        ];
    }
}
