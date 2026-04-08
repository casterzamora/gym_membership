<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFitnessClassRequest;
use App\Http\Requests\UpdateFitnessClassRequest;
use App\Models\FitnessClass;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;

class FitnessClassController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource with attendance counts and trainer info.
     */
    public function index()
    {
        $classes = FitnessClass::with('trainer', 'equipment', 'schedules')
            ->withCount('attendances')
            ->orderByDesc('attendances_count')
            ->get()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'class_name' => $class->class_name,
                    'description' => $class->description,
                    'max_participants' => $class->max_participants,
                    'difficulty_level' => $class->difficulty_level,
                    'trainer_id' => $class->trainer_id,
                    'current_enrolled' => $class->attendances_count,
                    'remaining_slots' => max(0, $class->max_participants - $class->attendances_count),
                    'is_full' => $class->attendances_count >= $class->max_participants,
                    'enrollment_percentage' => round(($class->attendances_count / $class->max_participants) * 100),
                    'trainer' => $class->trainer ? [
                        'id' => $class->trainer->id,
                        'first_name' => $class->trainer->first_name,
                        'last_name' => $class->trainer->last_name,
                        'specialization' => $class->trainer->specialization,
                    ] : null,
                    'schedules' => $class->schedules->map(fn($s) => [
                        'id' => $s->id,
                        'date' => $s->class_date,
                        'class_time' => $s->start_time,
                        'duration' => $s->duration ?? 60,
                    ]),
                    'equipment' => $class->equipment->map(fn($e) => [
                        'id' => $e->id,
                        'equipment_name' => $e->equipment_name,
                    ]),
                ];
            })
            ->values();

        return $this->success($classes, 'Fitness classes retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFitnessClassRequest $request)
    {
        try {
            $class = FitnessClass::create($request->validated());
            return $this->success($class->load('trainer', 'equipment'), 'Fitness class created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create fitness class: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FitnessClass $fitness_class)
    {
        $fitness_class->loadCount('attendances');
        return $this->success($fitness_class->load('trainer', 'equipment', 'schedules'), 'Fitness class retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFitnessClassRequest $request, FitnessClass $fitness_class)
    {
        try {
            $fitness_class->update($request->validated());
            return $this->success($fitness_class->load('trainer', 'equipment'), 'Fitness class updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update fitness class: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FitnessClass $fitness_class)
    {
        try {
            $fitness_class->delete();
            return $this->success(null, 'Fitness class deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete fitness class: ' . $e->getMessage(), null, 500);
        }
    }
}
