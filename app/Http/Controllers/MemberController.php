<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index()
    {
        return Member::with('membership', 'attendances')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:members',
            'phone' => 'required|unique:members',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
            'join_date' => 'required|date',
        ]);

        return Member::create($validated);
    }

    public function show(Member $member)
    {
        return $member->load('membership', 'attendances', 'payments');
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'name' => 'string',
            'email' => 'email|unique:members,email,' . $member->id,
            'phone' => 'unique:members,phone,' . $member->id,
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
            'status' => 'in:active,inactive,suspended',
        ]);

        $member->update($validated);
        return $member;
    }

    public function destroy(Member $member)
    {
        $member->delete();
        return response()->json(['message' => 'Member deleted'], 200);
    }

    public function getByStatus($status)
    {
        return Member::where('status', $status)->get();
    }
}
