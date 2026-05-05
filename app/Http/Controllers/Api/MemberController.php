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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Include related user so frontend can surface email/phone when available
        $query = Member::with('plan', 'attendances', 'payments', 'user')->latest('created_at');

        if ($request->user() instanceof User && $request->user()->role === 'trainer') {
            $trainerMemberIds = $this->resolveTrainerMemberIds($request->user());
            $query->whereIn('id', $trainerMemberIds);
        }

        $members = $query->paginate(15);

        // Ensure the paginated collection contains convenient top-level email/phone
        // attributes so older frontend code that expects `member.email` continues to work.
        $members->getCollection()->transform(function ($m) {
            $m->setAttribute('email', $m->user?->email ?? null);
            // Prefer phone on the related user if present, otherwise fall back to member.phone
            $m->setAttribute('phone', $m->user?->phone ?? $m->phone ?? null);
            // Expose plan name at top-level for frontend convenience
            // If plan is not loaded but plan_id is set, try to load it
            if (!$m->plan && $m->plan_id) {
                $m->load('plan');
            }
            $m->setAttribute('plan_name', $m->plan?->plan_name ?? null);
            return $m;
        });

        return $this->paginated($members, 'Members retrieved successfully');
    }

    /**
     * Search users by name or email to allow trainers to add existing users as members.
     */
    public function search(Request $request)
    {
        $q = $request->query('q') ?? $request->query('query') ?? '';
        $q = trim($q);

        if (strlen($q) < 2) {
            return $this->success([], 'No query provided');
        }

        // Search users by name/email
        $users = User::query()
            ->where(function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            })
            ->limit(50)
            ->get(['id', 'name', 'email']);

        // Map into simplified shape
        $results = $users->map(function ($u) {
            [$first, $last] = array_pad(preg_split('/\s+/', trim($u->name)), 2, '');
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'first_name' => $first,
                'last_name' => $last,
            ];
        });

        return $this->success($results, 'Search results');
    }

    /**
     * Store a newly created resource in storage.
     * Creates a member profile linked to an existing user
     */
    public function store(StoreMemberRequest $request)
    {
        try {
            $data = $request->validated();

            // If user_id not provided, create a placeholder User so members can be created by trainers
            if (empty($data['user_id'])) {
                $generatedEmail = sprintf('trainer-%s-student-%s@local.invalid', $request->user()?->trainer?->id ?? '0', Str::random(8));
                $user = User::create([
                    'name' => trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')) ?: 'New Member',
                    'email' => $generatedEmail,
                    'password' => Hash::make(Str::random(12)),
                ]);
                $data['user_id'] = $user->id;
            }

            // Provide sensible defaults for optional fields to avoid DB errors
            if (!isset($data['phone'])) {
                $data['phone'] = '';
            }
            // DB requires a non-null date_of_birth column — provide a safe default when not supplied
            if (!isset($data['date_of_birth']) || empty($data['date_of_birth'])) {
                $data['date_of_birth'] = '1900-01-01';
            }

            $member = Member::where('user_id', $data['user_id'])->first();
            if ($member) {
                $member->fill($data);
                $member->save();
                $statusCode = 200;
            } else {
                $member = Member::create($data);
                $statusCode = 201;
            }

            $member = $member->load('plan', 'user');
            if (!$member->plan && $member->plan_id) {
                $member->load('plan');
            }
            $member->setAttribute('email', $member->user?->email ?? null);
            $member->setAttribute('phone', $member->user?->phone ?? $member->phone ?? null);
            $member->setAttribute('plan_name', $member->plan?->plan_name ?? null);
            return $this->success($member, $statusCode === 201 ? 'Member created successfully' : 'Member updated successfully', $statusCode);
        } catch (\Exception $e) {
            return $this->error('Failed to create member: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $member = Member::with('plan', 'attendances', 'payments', 'user')->find($id);

        if (!$member) {
            return $this->notFound('Member not found');
        }

        if ($request->user() instanceof User && $request->user()->role === 'trainer') {
            $trainerMemberIds = $this->resolveTrainerMemberIds($request->user());

            if (!in_array((int) $member->id, $trainerMemberIds, true)) {
                return $this->error('Forbidden: trainers can only access members from their classes', null, 403);
            }
        }

        // Expose email/phone/plan on the returned member for frontend convenience
        if (!$member->plan && $member->plan_id) {
            $member->load('plan');
        }
        $member->setAttribute('email', $member->user?->email ?? null);
        $member->setAttribute('phone', $member->user?->phone ?? $member->phone ?? null);
        $member->setAttribute('plan_name', $member->plan?->plan_name ?? null);

        return $this->success($member, 'Member retrieved successfully');
    }

    /**
     * Resolve member ids tied to a trainer's assigned classes via attendance.
     */
    private function resolveTrainerMemberIds(User $user): array
    {
        $trainer = $user->trainer;

        if (!$trainer) {
            return [];
        }

        return Member::query()
            ->select('members.id')
            ->distinct()
            ->join('attendance', 'members.id', '=', 'attendance.member_id')
            ->join('class_schedules', 'attendance.schedule_id', '=', 'class_schedules.id')
            ->join('fitness_classes', 'class_schedules.class_id', '=', 'fitness_classes.id')
            ->where('fitness_classes.trainer_id', $trainer->id)
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

        if (!$member->membership_end) {
            return $this->error('You can only renew once your current membership expires.', [
                'membership_end' => null,
            ], 422);
        }

        $membershipExpiry = Carbon::parse($member->membership_end)->startOfDay();
        $today = now()->startOfDay();

        if ($today->lt($membershipExpiry)) {
            return $this->error(
                'You can only renew once your current membership expires on ' . $membershipExpiry->toDateString(),
                [
                    'eligible_date' => $membershipExpiry->toDateString(),
                    'membership_end' => $member->membership_end,
                ],
                422
            );
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
        try {
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

            if (!$member->membership_end && !$member->membership_start) {
                return $this->error('Member does not have a valid current membership period for upgrade eligibility', null, 422);
            }

            $membershipEnd = $member->membership_end
                ? Carbon::parse($member->membership_end)->startOfDay()->addDay()
                : Carbon::parse($member->membership_start)->startOfDay()->addMonthNoOverflow();

            if ($effectiveDate->lt($membershipEnd)) {
                return $this->error(
                    'You can only upgrade once your membership expires. Eligible on ' . $membershipEnd->toDateString(),
                    [
                        'eligible_date' => $membershipEnd->toDateString(),
                        'membership_end' => $member->membership_end,
                    ],
                    422
                );
            }

            $currentEnd = $member->membership_end ? Carbon::parse($member->membership_end)->startOfDay() : null;
            $start = $currentEnd && $currentEnd->gte($effectiveDate)
                ? $currentEnd->copy()->addDay()
                : $effectiveDate;
            $newEnd = $start->copy()->addMonthsNoOverflow((int) $newPlan->duration_months)->subDay();

            // Perform upgrade in transaction
            DB::transaction(function () use ($member, $newPlanId, $effectiveDate, $newEnd, $start): void {
                // Create upgrade history record
                $upgrade = MembershipUpgrade::create([
                    'member_id' => $member->id,
                    'old_plan_id' => $member->plan_id,
                    'new_plan_id' => $newPlanId,
                    'upgrade_date' => $effectiveDate->toDateString(),
                ]);

                if (!$upgrade) {
                    throw new \Exception('Failed to create membership upgrade record');
                }

                // Update member with new plan
                $updated = $member->update([
                    'plan_id' => $newPlanId,
                    'membership_start' => $member->membership_start ?: $start->toDateString(),
                    'membership_end' => $newEnd->toDateString(),
                    'membership_status' => 'active',
                ]);

                if (!$updated) {
                    throw new \Exception('Failed to update member plan information');
                }
            });

            // Refresh member data and return
            $updatedMember = $member->fresh()->load('plan', 'membershipUpgrades');
            return $this->success($updatedMember, 'Membership upgraded successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            \Log::error('Membership upgrade failed', [
                'member_id' => $member->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->error('Failed to upgrade membership: ' . $e->getMessage(), null, 500);
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

    private function forbidIfDifferentMemberActor($actor, Member $target)
    {
        if ($actor instanceof Member && (int) $actor->id !== (int) $target->id) {
            return $this->error('Forbidden: members can only modify their own membership', null, 403);
        }

        return null;
    }
}
