<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EquipmentTracking;
use App\Models\FitnessClass;
use App\Models\Equipment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class EquipmentTrackingController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of equipment tracking records
     */
    public function index(Request $request)
    {
        $query = EquipmentTracking::with(['equipment', 'fitnessClass', 'user', 'assignedBy', 'returnedBy']);

        // Filter by class
        if ($request->has('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter for required equipment only
        if ($request->boolean('required_only')) {
            $query->required();
        }

        // Filter for equipment in use only
        if ($request->boolean('in_use_only')) {
            $query->inUse();
        }

        $tracking = $query->paginate(15);
        return $this->paginated($tracking, 'Equipment tracking records retrieved successfully');
    }

    /**
     * Store a new equipment tracking record
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'class_id' => 'required|exists:fitness_classes,id',
                'equipment_id' => 'required|exists:equipment,id',
                'user_id' => 'nullable|exists:users,id',
                'quantity' => 'nullable|integer|min:1',
                'status' => 'required|in:required,in_use,returned',
                'used_at' => 'nullable|datetime',
                'returned_at' => 'nullable|datetime',
                'assigned_by' => 'nullable|exists:users,id',
                'returned_by' => 'nullable|exists:users,id',
                'notes' => 'nullable|string',
            ]);

            // Validation: user_id is required when status = 'in_use'
            if ($validated['status'] === 'in_use' && empty($validated['user_id'])) {
                return $this->error('user_id is required when status is "in_use"', null, 422);
            }

            // Get current user for audit trail
            $currentUser = auth()->user();

            // Prevent duplicate in_use equipment for same user
            if ($validated['status'] === 'in_use') {
                $existing = EquipmentTracking::where('class_id', $validated['class_id'])
                    ->where('equipment_id', $validated['equipment_id'])
                    ->where('user_id', $validated['user_id'])
                    ->where('status', 'in_use')
                    ->first();

                if ($existing) {
                    return $this->error('This equipment is already in use by this user in this class', null, 409);
                }
            }

            // Prevent duplicate required equipment for same class
            if ($validated['status'] === 'required') {
                $existing = EquipmentTracking::where('class_id', $validated['class_id'])
                    ->where('equipment_id', $validated['equipment_id'])
                    ->where('status', 'required')
                    ->whereNull('used_at')
                    ->first();

                if ($existing) {
                    return $this->error('Equipment already assigned to this class', null, 409);
                }
            }

            // Set assigned_by to current user if not provided
            if (empty($validated['assigned_by']) && $currentUser) {
                $validated['assigned_by'] = $currentUser->id;
            }

            $tracking = EquipmentTracking::create($validated);
            return $this->success(
                $tracking->load(['equipment', 'fitnessClass', 'user', 'assignedBy', 'returnedBy']),
                'Equipment tracking record created successfully',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->error('Failed to create equipment tracking record: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified equipment tracking record
     */
    public function show($id)
    {
        try {
            $tracking = EquipmentTracking::with(['equipment', 'fitnessClass', 'user', 'assignedBy', 'returnedBy'])->findOrFail($id);
            return $this->success($tracking, 'Equipment tracking record retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Equipment tracking record not found', null, 404);
        }
    }

    /**
     * Update the specified equipment tracking record
     */
    public function update(Request $request, $id)
    {
        try {
            $tracking = EquipmentTracking::findOrFail($id);

            $validated = $request->validate([
                'user_id' => 'nullable|exists:users,id',
                'quantity' => 'nullable|integer|min:1',
                'status' => 'nullable|in:required,in_use,returned',
                'used_at' => 'nullable|datetime',
                'returned_at' => 'nullable|datetime',
                'returned_by' => 'nullable|exists:users,id',
                'notes' => 'nullable|string',
            ]);

            // If status is changing to in_use and used_at is not provided, set it to now
            if ($validated['status'] === 'in_use' && empty($validated['used_at'])) {
                $validated['used_at'] = now();
            }

            // Validation: user_id is required when status = 'in_use'
            if ($validated['status'] === 'in_use' && empty($validated['user_id']) && !$tracking->user_id) {
                return $this->error('user_id is required when status is "in_use"', null, 422);
            }

            // If transitioning to returned, set returned_at if not provided
            if ($validated['status'] === 'returned' && empty($validated['returned_at'])) {
                $validated['returned_at'] = now();
            }

            // Set returned_by to current user if not provided
            if ($validated['status'] === 'returned' && empty($validated['returned_by'])) {
                $currentUser = auth()->user();
                if ($currentUser) {
                    $validated['returned_by'] = $currentUser->id;
                }
            }

            $tracking->update($validated);
            return $this->success(
                $tracking->load(['equipment', 'fitnessClass', 'user', 'assignedBy', 'returnedBy']),
                'Equipment tracking record updated successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Equipment tracking record not found', null, 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->error('Failed to update equipment tracking record: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified equipment tracking record
     */
    public function destroy($id)
    {
        try {
            $tracking = EquipmentTracking::findOrFail($id);
            $tracking->delete();
            return $this->success(null, 'Equipment tracking record deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Equipment tracking record not found', null, 404);
        } catch (\Exception $e) {
            return $this->error('Failed to delete equipment tracking record: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get required equipment for a class
     */
    public function getClassEquipment($classId)
    {
        try {
            FitnessClass::findOrFail($classId);

            $equipment = EquipmentTracking::forClass($classId)
                ->required()
                ->with(['equipment', 'user', 'assignedBy'])
                ->get();

            return $this->success(
                $equipment,
                'Required equipment for class retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Class not found', null, 404);
        }
    }

    /**
     * Mark equipment as in use for a schedule
     */
    public function markAsInUse(Request $request, $id)
    {
        try {
            $tracking = EquipmentTracking::findOrFail($id);
            
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);

            $currentUser = auth()->user();
            $tracking->markAsInUse($validated['user_id'], $currentUser?->id);

            return $this->success(
                $tracking->load(['equipment', 'fitnessClass', 'user', 'assignedBy']),
                'Equipment marked as in use'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Equipment tracking record not found', null, 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->error('Failed to mark equipment as in use: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Mark equipment as returned
     */
    public function markAsReturned(Request $request, $id)
    {
        try {
            $tracking = EquipmentTracking::findOrFail($id);
            $currentUser = auth()->user();
            $tracking->markAsReturned($currentUser?->id);

            return $this->success(
                $tracking->load(['equipment', 'fitnessClass', 'user', 'returnedBy']),
                'Equipment marked as returned'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Equipment tracking record not found', null, 404);
        } catch (\Exception $e) {
            return $this->error('Failed to mark equipment as returned: ' . $e->getMessage(), null, 500);
        }
    }
}
