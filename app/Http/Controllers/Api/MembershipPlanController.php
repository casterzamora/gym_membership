<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMembershipPlanRequest;
use App\Http\Requests\UpdateMembershipPlanRequest;
use App\Models\MembershipPlan;
use App\Traits\ApiResponse;

class MembershipPlanController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plans = MembershipPlan::paginate(15);
        return $this->paginated($plans, 'Membership plans retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMembershipPlanRequest $request)
    {
        try {
            $plan = MembershipPlan::create($request->validated());
            return $this->success($plan, 'Membership plan created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create membership plan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MembershipPlan $plan)
    {
        return $this->success($plan, 'Membership plan retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMembershipPlanRequest $request, MembershipPlan $plan)
    {
        try {
            $plan->update($request->validated());
            return $this->success($plan, 'Membership plan updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update membership plan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MembershipPlan $plan)
    {
        try {
            $plan->delete();
            return $this->success(null, 'Membership plan deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete membership plan: ' . $e->getMessage(), null, 500);
        }
    }
}
