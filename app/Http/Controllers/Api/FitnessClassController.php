<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFitnessClassRequest;
use App\Http\Requests\UpdateFitnessClassRequest;
use App\Models\FitnessClass;
use App\Traits\ApiResponse;

class FitnessClassController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $classes = FitnessClass::with('trainer.user', 'equipment')->paginate(15);
        return $this->paginated($classes, 'Fitness classes retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFitnessClassRequest $request)
    {
        try {
            $class = FitnessClass::create($request->validated());
            return $this->success($class->load('trainer.user', 'equipment'), 'Fitness class created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create fitness class: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FitnessClass $fitness_class)
    {
        return $this->success($fitness_class->load('trainer.user', 'equipment'), 'Fitness class retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFitnessClassRequest $request, FitnessClass $fitness_class)
    {
        try {
            $fitness_class->update($request->validated());
            return $this->success($fitness_class->load('trainer.user', 'equipment'), 'Fitness class updated successfully');
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
