<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassScheduleRequest;
use App\Http\Requests\UpdateClassScheduleRequest;
use App\Models\ClassSchedule;
use App\Traits\ApiResponse;

class ClassScheduleController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schedules = ClassSchedule::with('fitnessClass.trainer.user', 'attendances')->paginate(15);
        return $this->paginated($schedules, 'Class schedules retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClassScheduleRequest $request)
    {
        try {
            $schedule = ClassSchedule::create($request->validated());
            return $this->success($schedule->load('fitnessClass', 'attendance'), 'Class schedule created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create class schedule: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ClassSchedule $class_schedule)
    {
        return $this->success($class_schedule->load('fitnessClass.trainer.user', 'attendances'), 'Class schedule retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClassScheduleRequest $request, ClassSchedule $class_schedule)
    {
        try {
            $class_schedule->update($request->validated());
            return $this->success($class_schedule->load('fitnessClass.trainer.user', 'attendances'), 'Class schedule updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update class schedule: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClassSchedule $class_schedule)
    {
        try {
            $class_schedule->delete();
            return $this->success(null, 'Class schedule deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete class schedule: ' . $e->getMessage(), null, 500);
        }
    }
}
