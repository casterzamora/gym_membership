<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassScheduleRequest;
use App\Http\Requests\UpdateClassScheduleRequest;
use App\Models\ClassSchedule;
use App\Models\Trainer;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ClassScheduleController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all schedules without pagination when needed (frontend will filter by trainer)
        // Use paginate(100) to get more results while still being memory-safe
        $schedules = ClassSchedule::select('id', 'class_id', 'class_date', 'start_time', 'end_time', 'recurrence_type', 'recurrence_end_date', 'created_at', 'updated_at')->paginate(100);
        return $this->paginated($schedules, 'Class schedules retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClassScheduleRequest $request)
    {
        try {
            $actor = $request->user();
            $data = $request->validated();

            if ($actor instanceof User && $actor->role === 'trainer') {
                $trainer = $actor->trainer;

                if (!$trainer) {
                    return $this->error('Forbidden: trainer profile not found', null, 403);
                }

                if (!$this->trainerOwnsClass($trainer, (int) $data['class_id'])) {
                    return $this->error('Forbidden: trainers can only create schedules for their own classes', null, 403);
                }
            }

            \Log::info('Creating schedule', ['data' => $data]);
            
            $schedule = ClassSchedule::create($data);
            
            \Log::info('Schedule created', ['id' => $schedule->id, 'data' => $schedule->toArray()]);
            
            // Return the created schedule
            return $this->success($schedule->toArray(), 'Class schedule created successfully', 201);
        } catch (\Exception $e) {
            \Log::error('Schedule creation error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->error('Failed to create class schedule: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ClassSchedule $schedule)
    {
        return $this->success($schedule, 'Class schedule retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClassScheduleRequest $request, ClassSchedule $schedule)
    {
        try {
            $actor = $request->user();
            $data = $request->validated();

            if ($actor instanceof User && $actor->role === 'trainer') {
                $trainer = $actor->trainer;

                if (!$trainer) {
                    return $this->error('Forbidden: trainer profile not found', null, 403);
                }

                if (!$this->scheduleBelongsToTrainer($schedule, $trainer)) {
                    return $this->error('Forbidden: trainers can only update schedules for their own classes', null, 403);
                }

                if (isset($data['class_id']) && !$this->trainerOwnsClass($trainer, (int) $data['class_id'])) {
                    return $this->error('Forbidden: trainers can only move schedules to their own classes', null, 403);
                }
            }

            \Log::info('Update started', ['id' => $schedule->id]);

            // Perform the update
            $schedule->update($data);

            // Reload the model from the database
            $schedule->refresh();

            \Log::info('Schedule updated and refreshed', [
                'id' => $schedule->id,
                'start_time' => $schedule->start_time,
                'model_array' => $schedule->toArray()
            ]);

            // Return the refreshed model as array
            return $this->success($schedule->toArray(), 'Class schedule updated successfully');
        } catch (\Exception $e) {
            \Log::error('Schedule update error: ' . $e->getMessage(), [
                'schedule_id' => $schedule->id ?? 'unknown',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return $this->error('Failed to update class schedule: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClassSchedule $schedule)
    {
        try {
            $actor = request()->user();

            if ($actor instanceof User && $actor->role === 'trainer') {
                $trainer = $actor->trainer;

                if (!$trainer) {
                    return $this->error('Forbidden: trainer profile not found', null, 403);
                }

                if (!$this->scheduleBelongsToTrainer($schedule, $trainer)) {
                    return $this->error('Forbidden: trainers can only delete schedules for their own classes', null, 403);
                }
            }

            $schedule->delete();
            return $this->success(null, 'Class schedule deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete class schedule: ' . $e->getMessage(), null, 500);
        }
    }

    private function scheduleBelongsToTrainer(ClassSchedule $schedule, Trainer $trainer): bool
    {
        return (int) optional($schedule->fitnessClass)->trainer_id === (int) $trainer->id;
    }

    private function trainerOwnsClass(Trainer $trainer, int $classId): bool
    {
        return $trainer->classes()->whereKey($classId)->exists();
    }
}
