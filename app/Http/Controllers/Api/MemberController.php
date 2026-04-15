<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\MembershipUpgrade;
use App\Models\Trainer;
use App\Models\User;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Member::with('plan', 'attendances', 'payments')->latest('created_at');

        if ($request->user() instanceof User && $request->user()->role === 'trainer') {
            $trainerMemberIds = $this->resolveTrainerMemberIds($request->user());
            $query->whereIn('id', $trainerMemberIds);
        }

        $members = $query->paginate(15);
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
    public function show(Request $request, $id)
    {
        $member = Member::with('plan', 'attendances', 'payments')->find($id);

        if (!$member) {
            return $this->notFound('Member not found');
        }

        if ($request->user() instanceof User && $request->user()->role === 'trainer') {
            $trainerMemberIds = $this->resolveTrainerMemberIds($request->user());

            if (!in_array((int) $member->id, $trainerMemberIds, true)) {
                return $this->error('Forbidden: trainers can only access members from their classes', null, 403);
            }
        }

        return $this->success($member, 'Member retrieved successfully');
    }

    /**
     * Resolve member ids tied to a trainer's assigned classes via attendance.
     */
    private function resolveTrainerMemberIds(User $user): array
    {
        $trainer = Trainer::where('email', $user->email)->first();

        if (!$trainer) {
            return [];
        }

        return Member::query()
            ->join('attendance', 'members.id', '=', 'attendance.member_id')
            ->join('class_schedules', 'attendance.schedule_id', '=', 'class_schedules.id')
            ->join('fitness_classes', 'class_schedules.class_id', '=', 'fitness_classes.id')
            ->where('fitness_classes.trainer_id', $trainer->id)
            ->distinct()
            ->pluck('members.id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
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
     * Renew member using current membership plan duration.
     */
    public function renew(Request $request, Member $member)
    {
        $forbidden = $this->forbidIfDifferentMemberActor($request->user(), $member);
        if ($forbidden !== null) {
            return $forbidden;
        }

        if (!$member->plan_id) {
            return $this->error('Cannot renew membership without an assigned plan', null, 422);
        }

        $validated = $request->validate([
            'start_date' => 'nullable|date',
        ]);

        $plan = MembershipPlan::find($member->plan_id);
        if (!$plan) {
            return $this->error('Assigned membership plan not found', null, 422);
        }

        $anchorDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : now()->startOfDay();

        $currentEnd = $member->membership_end ? Carbon::parse($member->membership_end)->startOfDay() : null;
        $start = $currentEnd && $currentEnd->gte($anchorDate)
            ? $currentEnd->copy()->addDay()
            : $anchorDate;

        $newEnd = $start->copy()->addMonthsNoOverflow((int) $plan->duration_months)->subDay();

        $member->update([
            'membership_start' => $member->membership_start ?: $start->toDateString(),
            'membership_end' => $newEnd->toDateString(),
            'membership_status' => 'active',
        ]);

        return $this->success($member->fresh()->load('plan'), 'Membership renewed successfully');
    }

    /**
     * Upgrade a member to a new plan and persist upgrade history.
     */
    public function upgrade(Request $request, Member $member)
    {
        $forbidden = $this->forbidIfDifferentMemberActor($request->user(), $member);
        if ($forbidden !== null) {
            return $forbidden;
        }

        $validated = $request->validate([
            'new_plan_id' => 'required|exists:membership_plans,id',
            'effective_date' => 'nullable|date',
        ]);

        $newPlanId = (int) $validated['new_plan_id'];
        if ((int) $member->plan_id === $newPlanId) {
            return $this->error('Member is already enrolled in this plan', null, 422);
        }

        $newPlan = MembershipPlan::find($newPlanId);
        if (!$newPlan) {
            return $this->error('New membership plan not found', null, 422);
        }

        $effectiveDate = isset($validated['effective_date'])
            ? Carbon::parse($validated['effective_date'])->startOfDay()
            : now()->startOfDay();

        $currentEnd = $member->membership_end ? Carbon::parse($member->membership_end)->startOfDay() : null;
        $start = $currentEnd && $currentEnd->gte($effectiveDate)
            ? $currentEnd->copy()->addDay()
            : $effectiveDate;
        $newEnd = $start->copy()->addMonthsNoOverflow((int) $newPlan->duration_months)->subDay();

        DB::transaction(function () use ($member, $newPlanId, $effectiveDate, $newEnd, $start): void {
            MembershipUpgrade::create([
                'member_id' => $member->id,
                'old_plan_id' => $member->plan_id,
                'new_plan_id' => $newPlanId,
                'upgrade_date' => $effectiveDate->toDateString(),
            ]);

            $member->update([
                'plan_id' => $newPlanId,
                'membership_start' => $member->membership_start ?: $start->toDateString(),
                'membership_end' => $newEnd->toDateString(),
                'membership_status' => 'active',
            ]);
        });

        return $this->success($member->fresh()->load('plan', 'membershipUpgrades'), 'Membership upgraded successfully');
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

    private function forbidIfDifferentMemberActor($actor, Member $target)
    {
        if ($actor instanceof Member && (int) $actor->id !== (int) $target->id) {
            return $this->error('Forbidden: members can only modify their own membership', null, 403);
        }

        return null;
    }
}
