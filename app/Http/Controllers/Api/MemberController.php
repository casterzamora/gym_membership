<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use App\Traits\ApiResponse;

class MemberController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $members = Member::with('user', 'plan', 'attendances')->paginate(15);
        return $this->paginated($members, 'Members retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMemberRequest $request)
    {
        try {
            $member = Member::create($request->validated());
            return $this->success($member->load('user', 'plan'), 'Member created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create member: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($idOrUserId)
    {
        // Try to find by member ID first
        $member = Member::find($idOrUserId);
        
        // If not found, try to find by user_id
        if (!$member) {
            $member = Member::where('user_id', $idOrUserId)->first();
        }

        if (!$member) {
            return $this->notFound('Member not found');
        }

        return $this->success($member->load('user', 'plan', 'attendances'), 'Member retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMemberRequest $request, Member $member)
    {
        try {
            $member->update($request->validated());
            return $this->success($member->load('user', 'plan'), 'Member updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update member: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member)
    {
        try {
            $member->delete();
            return $this->success(null, 'Member deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete member: ' . $e->getMessage(), null, 500);
        }
    }
}
