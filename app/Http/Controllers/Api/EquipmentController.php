<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Models\Equipment;
use App\Traits\ApiResponse;

class EquipmentController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $equipment = Equipment::all();
        return $this->success($equipment, 'Equipment retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEquipmentRequest $request)
    {
        try {
            $equipment = Equipment::create($request->validated());
            return $this->success($equipment, 'Equipment created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create equipment: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Equipment $equipment)
    {
        return $this->success($equipment, 'Equipment retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEquipmentRequest $request, Equipment $equipment)
    {
        try {
            $equipment->update($request->validated());
            return $this->success($equipment, 'Equipment updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update equipment: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Equipment $equipment)
    {
        try {
            $equipment->delete();
            return $this->success(null, 'Equipment deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete equipment: ' . $e->getMessage(), null, 500);
        }
    }
}
