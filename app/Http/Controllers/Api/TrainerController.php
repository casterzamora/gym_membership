<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrainerRequest;
use App\Http\Requests\UpdateTrainerRequest;
use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\Trainer;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class TrainerController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $trainers = Trainer::with('certifications', 'classes')->paginate(15);
        return $this->paginated($trainers, 'Trainers retrieved successfully');
    }

    /**
     * Aggregated workload metrics for a specific trainer.
     */
    public function workload(Request $request, Trainer $trainer)
    {
        $forbidden = $this->forbidIfDifferentTrainerActor($request, $trainer);
        if ($forbidden !== null) {
            return $forbidden;
        }

        $classIds = $trainer->classes()->pluck('id');
        $totalClasses = $classIds->count();

        $totalSchedules = ClassSchedule::query()->whereIn('class_id', $classIds)->count();
        $upcomingSchedules = ClassSchedule::query()
            ->whereIn('class_id', $classIds)
            ->whereDate('class_date', '>=', now()->toDateString())
            ->count();

        $attendanceBase = Attendance::query()
            ->join('class_schedules', 'attendance.schedule_id', '=', 'class_schedules.id')
            ->whereIn('class_schedules.class_id', $classIds);

        $totalAttendance = (clone $attendanceBase)->count();
        $uniqueMembers = (clone $attendanceBase)
            ->distinct('attendance.member_id')
            ->count('attendance.member_id');

        $attendanceByStatusRows = (clone $attendanceBase)
            ->selectRaw('attendance.attendance_status, COUNT(*) as total')
            ->groupBy('attendance.attendance_status')
            ->get();

        $attendanceByStatus = [
            'Present' => 0,
            'Absent' => 0,
            'Late' => 0,
        ];

        foreach ($attendanceByStatusRows as $row) {
            $attendanceByStatus[$row->attendance_status] = (int) $row->total;
        }

        $payload = [
            'trainer' => [
                'id' => $trainer->id,
                'name' => trim($trainer->first_name . ' ' . $trainer->last_name),
                'email' => $trainer->email,
            ],
            'metrics' => [
                'total_classes' => $totalClasses,
                'total_schedules' => $totalSchedules,
                'upcoming_schedules' => $upcomingSchedules,
                'total_attendance_records' => $totalAttendance,
                'unique_members' => $uniqueMembers,
                'average_attendance_per_schedule' => $totalSchedules > 0
                    ? round($totalAttendance / $totalSchedules, 2)
                    : 0.0,
            ],
            'attendance_by_status' => $attendanceByStatus,
        ];

        return $this->success($payload, 'Trainer workload retrieved successfully');
    }

    /**
     * Summary workload metrics for all trainers.
     */
    public function workloadSummary(Request $request)
    {
        // Admin-only endpoint guarded at route-level; keep response deterministic.
        $trainers = Trainer::query()->orderBy('id')->get();

        $summary = $trainers->map(function (Trainer $trainer) {
            $classIds = $trainer->classes()->pluck('id');
            $totalSchedules = ClassSchedule::query()->whereIn('class_id', $classIds)->count();

            $totalAttendance = Attendance::query()
                ->join('class_schedules', 'attendance.schedule_id', '=', 'class_schedules.id')
                ->whereIn('class_schedules.class_id', $classIds)
                ->count();

            return [
                'trainer_id' => $trainer->id,
                'trainer_name' => trim($trainer->first_name . ' ' . $trainer->last_name),
                'total_classes' => $classIds->count(),
                'total_schedules' => $totalSchedules,
                'total_attendance_records' => $totalAttendance,
                'average_attendance_per_schedule' => $totalSchedules > 0
                    ? round($totalAttendance / $totalSchedules, 2)
                    : 0.0,
            ];
        })->values();

        return $this->success($summary, 'Trainer workload summary retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTrainerRequest $request)
    {
        try {
            $trainer = Trainer::create($request->validated());
            return $this->success($trainer->load('certifications', 'classes'), 'Trainer created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create trainer: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Trainer $trainer)
    {
        return $this->success($trainer->load('certifications', 'classes'), 'Trainer retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTrainerRequest $request, Trainer $trainer)
    {
        try {
            $trainer->update($request->validated());
            return $this->success($trainer->load('certifications', 'classes'), 'Trainer updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update trainer: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trainer $trainer)
    {
        try {
            $trainer->delete();
            return $this->success(null, 'Trainer deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete trainer: ' . $e->getMessage(), null, 500);
        }
    }

    private function forbidIfDifferentTrainerActor(Request $request, Trainer $trainer)
    {
        $actor = $request->user();

        if ($actor instanceof User && $actor->role === 'trainer') {
            if (strcasecmp((string) $actor->email, (string) $trainer->email) !== 0) {
                return $this->error('Forbidden: trainers can only view their own workload', null, 403);
            }
        }

        return null;
    }
}
