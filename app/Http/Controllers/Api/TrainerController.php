<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrainerRequest;
use App\Http\Requests\UpdateTrainerRequest;
use App\Models\Trainer;
use App\Traits\ApiResponse;

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
        return $this->success($trainer->load('user', 'certifications', 'classes'), 'Trainer retrieved successfully');
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
}
