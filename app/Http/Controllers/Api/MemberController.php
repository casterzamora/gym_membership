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
        $members = Member::with('plan', 'attendances', 'payments')->latest('created_at')->paginate(15);
        return $this->paginated($members, 'Members retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMemberRequest $request)
    {
        try {
            $data = $request->validated();
            // Hash the password if provided
            if (isset($data['password_hash'])) {
                $data['password_hash'] = \Illuminate\Support\Facades\Hash::make($data['password_hash']);
            }
            $member = Member::create($data);
            return $this->success($member->load('plan'), 'Member created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create member: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $member = Member::with('plan', 'attendances', 'payments')->find($id);

        if (!$member) {
            return $this->notFound('Member not found');
        }

        return $this->success($member, 'Member retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMemberRequest $request, Member $member)
    {
        try {
            $data = $request->validated();
            // Hash password if being updated
            if (isset($data['password_hash'])) {
                $data['password_hash'] = \Illuminate\Support\Facades\Hash::make($data['password_hash']);
            }
            $member->update($data);
            return $this->success($member->load('plan'), 'Member updated successfully');
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
