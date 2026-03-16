<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\Member;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function index()
    {
        return Membership::with('member', 'package')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'package_id' => 'required|exists:packages,id',
            'duration_months' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        $membership = Membership::create([
            ...$validated,
            'start_date' => today(),
            'end_date' => today()->addMonths($validated['duration_months']),
            'status' => 'active',
        ]);

        return response()->json($membership, 201);
    }

    public function show(Membership $membership)
    {
        return $membership->load('member', 'package');
    }

    public function renew(Request $request, Membership $membership)
    {
        $validated = $request->validate([
            'duration_months' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        $newMembership = Membership::create([
            'member_id' => $membership->member_id,
            'package_id' => $membership->package_id,
            'start_date' => $membership->end_date->addDay(),
            'end_date' => $membership->end_date->addMonths($validated['duration_months']),
            'duration_months' => $validated['duration_months'],
            'price' => $validated['price'],
            'status' => 'active',
        ]);

        $membership->update(['status' => 'expired']);

        return response()->json($newMembership, 201);
    }

    public function cancel(Membership $membership)
    {
        $membership->update(['status' => 'cancelled']);
        return response()->json($membership, 200);
    }

    public function getActiveMemberships()
    {
        return Membership::where('status', 'active')
            ->where('end_date', '>=', now()->toDateString())
            ->with('member', 'package')
            ->get();
    }

    public function getExpiringMemberships(Request $request)
    {
        $days = $request->query('days', 7);
        $expiryDate = now()->addDays($days)->toDateString();

        return Membership::where('status', 'active')
            ->where('end_date', '<=', $expiryDate)
            ->where('end_date', '>=', now()->toDateString())
            ->with('member', 'package')
            ->get();
    }
}
