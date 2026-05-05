<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrainerRequest;
use App\Http\Requests\UpdateTrainerRequest;
use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
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
        $trainers = Trainer::with('user', 'certifications', 'classes')->paginate(15);
        return $this->paginated($trainers, 'Trainers retrieved successfully');
    }

    /**
     * Display a specific trainer resource.
     */
    public function show(Request $request, $trainer)
    {
        $trainerId = $trainer instanceof Trainer ? $trainer->id : $trainer;
        $trainerModel = Trainer::find($trainerId);

        if (!$trainerModel) {
            return $this->error('Trainer not found', null, 404);
        }

        $forbidden = $this->forbidIfDifferentTrainerActor($request, $trainerModel);
        if ($forbidden !== null) {
            return $forbidden;
        }

        return $this->success($trainerModel->load('user', 'certifications', 'classes'), 'Trainer retrieved successfully');
    }

    /**
     * Aggregated workload metrics for a specific trainer.
     */
    public function workload(Request $request, $trainer)
    {
        $trainerId = $trainer instanceof Trainer ? $trainer->id : $trainer;
        $trainer = Trainer::find($trainerId);
        if (!$trainer) {
            return $this->error('Trainer not found', null, 404);
        }
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
                'email' => $trainer->email,  // Now uses accessor
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
     * Creates a trainer and automatically creates/links a user account
     */
    public function store(StoreTrainerRequest $request)
    {
        try {
            $data = $request->validated();

            // Prevent trainers from updating their own hourly_rate (salary) via the API.
            $actor = $request->user();
            if ($actor instanceof User && $actor->role === 'trainer') {
                if (isset($data['hourly_rate'])) {
                    unset($data['hourly_rate']);
                }
            }
            
            // If no user_id provided, create a new user for this trainer
            if (!isset($data['user_id'])) {
                // Create user account for trainer with default password "password"
                // Trainer can change their password after first login
                $user = \App\Models\User::create([
                    'name' => trim($data['first_name'] . ' ' . $data['last_name']),
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'phone' => $data['phone'] ?? null,
                    'specialization' => $data['specialization'] ?? null,
                    'hourly_rate' => $data['hourly_rate'] ?? 0,
                    'role' => 'trainer',
                    'is_active' => true,
                ]);
                $data['user_id'] = $user->id;
            }
            
            $trainer = Trainer::create($data);
            return $this->success($trainer->load('certifications', 'classes', 'user'), 'Trainer created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create trainer: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTrainerRequest $request, $trainer)
    {
        try {
            $trainerId = $trainer instanceof Trainer ? $trainer->id : $trainer;
            $trainerModel = Trainer::find($trainerId);
            if (!$trainerModel) {
                return $this->error('Trainer not found', null, 404);
            }

            $forbidden = $this->forbidIfDifferentTrainerActor($request, $trainerModel);
            if ($forbidden !== null) {
                return $forbidden;
            }
            
            $data = $request->validated();
            
            // Update trainer record
            $trainerModel->update($data);
            
            // Sync user profile with trainer data if user exists
            if ($trainerModel->user_id) {
                $userUpdateData = [];
                if (isset($data['first_name']) || isset($data['last_name'])) {
                    $firstName = $data['first_name'] ?? $trainerModel->user->first_name;
                    $lastName = $data['last_name'] ?? $trainerModel->user->last_name;
                    $userUpdateData['name'] = trim($firstName . ' ' . $lastName);
                    if (isset($data['first_name'])) {
                        $userUpdateData['first_name'] = $data['first_name'];
                    }
                    if (isset($data['last_name'])) {
                        $userUpdateData['last_name'] = $data['last_name'];
                    }
                }
                if (isset($data['phone'])) {
                    $userUpdateData['phone'] = $data['phone'];
                }
                if (isset($data['specialization'])) {
                    $userUpdateData['specialization'] = $data['specialization'];
                }
                if (isset($data['hourly_rate'])) {
                    $userUpdateData['hourly_rate'] = $data['hourly_rate'];
                }
                
                if (!empty($userUpdateData)) {
                    $trainerModel->user->update($userUpdateData);
                }

                // Handle certifications array if provided (array of cert objects or ids)
                if ($request->has('certifications')) {
                    $certs = $request->input('certifications');
                    if (is_array($certs)) {
                        $attachIds = [];
                        foreach ($certs as $c) {
                            if (is_numeric($c)) {
                                $attachIds[] = (int) $c;
                                continue;
                            }

                            // Expect object with cert_name at minimum
                            if (is_array($c) && !empty($c['cert_name'])) {
                                $cert = \App\Models\Certification::firstOrCreate([
                                    'cert_name' => $c['cert_name'],
                                    'cert_number' => $c['cert_number'] ?? null,
                                ], [
                                    'issuing_organization' => $c['issuing_organization'] ?? null,
                                    'issue_date' => $c['issue_date'] ?? null,
                                    'expiry_date' => $c['expiry_date'] ?? null,
                                ]);
                                $attachIds[] = $cert->id;
                            }
                        }

                        if (!empty($attachIds)) {
                            $trainerModel->certifications()->sync($attachIds);
                        }
                    }
                }
            }
            
            return $this->success($trainerModel->load('certifications', 'classes', 'user'), 'Trainer updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update trainer: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($trainer)
    {
        try {
            $trainerId = $trainer instanceof Trainer ? $trainer->id : $trainer;
            $trainerModel = Trainer::find($trainerId);
            if (!$trainerModel) {
                return $this->error('Trainer not found', null, 404);
            }
            
            // Deactivate associated user account instead of deleting it
            // This preserves audit trails and payment records
            if ($trainerModel->user_id) {
                $trainerModel->user->update(['is_active' => false]);
            }
            
            // Delete trainer record
            $trainerModel->delete();
            return $this->success(null, 'Trainer deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete trainer: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Detach a certification from a trainer
     */
    public function destroyCertification(Request $request, $trainer, $cert)
    {
        try {
            $trainerId = $trainer instanceof Trainer ? $trainer->id : $trainer;
            $trainerModel = Trainer::find($trainerId);
            if (!$trainerModel) {
                return $this->error('Trainer not found', null, 404);
            }

            $forbidden = $this->forbidIfDifferentTrainerActor($request, $trainerModel);
            if ($forbidden !== null) {
                return $forbidden;
            }

            $certId = (int) $cert;
            $trainerModel->certifications()->detach($certId);
            return $this->success(null, 'Certification removed successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to remove certification: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Generate and return a temporary password for a trainer.
     * Admin-only endpoint for resetting trainer passwords.
     */
    public function resetPassword(Request $request, $trainer)
    {
        try {
            $trainerId = $trainer instanceof Trainer ? $trainer->id : $trainer;
            $trainerModel = Trainer::find($trainerId);
            
            if (!$trainerModel) {
                return $this->error('Trainer not found', null, 404);
            }

            // Generate a temporary 12-character password
            $tempPassword = Str::random(12);
            
            // Update the user's password
            if ($trainerModel->user_id) {
                $trainerModel->user->update([
                    'password' => \Illuminate\Support\Facades\Hash::make($tempPassword),
                ]);
            }
            
            return $this->success(
                ['temporary_password' => $tempPassword],
                'Temporary password generated. Share this with the trainer.',
                200
            );
        } catch (\Exception $e) {
            return $this->error('Failed to reset password: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Change password for a trainer (trainer or admin only)
     */
    public function changePassword(Request $request, $trainer)
    {
        try {
            $trainerId = $trainer instanceof Trainer ? $trainer->id : $trainer;
            $trainerModel = Trainer::find($trainerId);
            
            if (!$trainerModel) {
                return $this->error('Trainer not found', null, 404);
            }

            // Check permissions - trainer can only change their own password, admin can change any
            $actor = $request->user();
            if ($actor instanceof User && $actor->role === 'trainer') {
                $actorTrainer = $actor->trainer;
                if (!$actorTrainer || $actorTrainer->id !== $trainerModel->id) {
                    return $this->error('Forbidden: trainers can only change their own password', null, 403);
                }
            }

            // Validate request
            $validated = $request->validate([
                'current_password' => 'required_if:actor_role,trainer|string',  // Only required if trainer (not admin)
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            // If trainer is changing their own password, verify current password
            if ($actor instanceof User && $actor->role === 'trainer') {
                if (!Hash::check($validated['current_password'], $trainerModel->user->password)) {
                    return $this->error('Current password is incorrect', null, 401);
                }
            }

            // Update password
            if ($trainerModel->user_id) {
                $trainerModel->user->update([
                    'password' => Hash::make($validated['new_password']),
                ]);
            }
            
            return $this->success(null, 'Password changed successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error('Validation error', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->error('Failed to change password: ' . $e->getMessage(), null, 500);
        }
    }

    private function forbidIfDifferentTrainerActor(Request $request, Trainer $trainer)
    {
        $actor = $request->user();

        // Check if the actor is a trainer role and verify they're accessing their own workload
        if ($actor instanceof User && $actor->role === 'trainer') {
            // Get the trainer profile for this user
            $actorTrainer = $actor->trainer;
            
            if (!$actorTrainer || $actorTrainer->id !== $trainer->id) {
                return $this->error('Forbidden: trainers can only view their own workload', null, 403);
            }
        }

        return null;
    }
}
