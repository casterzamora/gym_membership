<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Http\Requests\StoreFitnessClassRequest;
use App\Http\Requests\UpdateFitnessClassRequest;
use App\Models\FitnessClass;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FitnessClassController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource with attendance counts and trainer info.
     */
    public function index(Request $request)
    {
        $actor = $request->user();
        $memberPlanId = null;

        if ($actor instanceof Member) {
            $memberPlanId = $actor->plan_id;
        } elseif ($actor instanceof \App\Models\User && $actor->role === 'member') {
            $memberPlanId = $actor->member?->plan_id;
        }

        $query = FitnessClass::with('trainer', 'schedules', 'membershipPlans')
            ->withCount('attendances')
            ->orderByDesc('attendances_count');

        if ($memberPlanId) {
            $query->whereHas('membershipPlans', function ($builder) use ($memberPlanId) {
                $builder->where('membership_plans.id', $memberPlanId);
            });
        } elseif ($actor instanceof Member || ($actor instanceof \App\Models\User && $actor->role === 'member')) {
            $query->whereRaw('1 = 0');
        }

        $classes = $query->get()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'class_name' => $class->class_name,
                    'description' => $class->description,
                    'max_participants' => $class->max_participants,
                    'difficulty_level' => $class->difficulty_level,
                    'is_special' => (bool) $class->is_special,
                    'trainer_id' => $class->trainer_id,
                    'current_enrolled' => $class->attendances_count,
                    'remaining_slots' => max(0, $class->max_participants - $class->attendances_count),
                    'is_full' => $class->attendances_count >= $class->max_participants,
                    'enrollment_percentage' => round(($class->attendances_count / $class->max_participants) * 100),
                    'trainer' => $class->trainer ? [
                        'id' => $class->trainer->id,
                        'first_name' => $class->trainer->first_name,
                        'last_name' => $class->trainer->last_name,
                        'specialization' => $class->trainer->specialization,
                    ] : null,
                    'schedules' => $class->schedules->map(fn($s) => [
                        'id' => $s->id,
                        'date' => $s->class_date,
                        'class_time' => $s->start_time,
                        'duration' => $s->duration ?? 60,
                    ]),
                    'membership_plan_ids' => $class->membershipPlans->pluck('id')->values(),
                    'membership_plans' => $class->membershipPlans->map(fn ($plan) => [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'plan_name' => $plan->plan_name,
                    ])->values(),
                ];
            })
            ->values();

        return $this->success($classes, 'Fitness classes retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFitnessClassRequest $request)
    {
        try {
            $data = $request->validated();
            $membershipPlanIds = $data['membership_plan_ids'] ?? [];
            unset($data['membership_plan_ids']);

            $class = DB::transaction(function () use ($data, $membershipPlanIds) {
                $class = FitnessClass::create($data);
                $this->syncMembershipPlans($class, $membershipPlanIds, (bool) ($data['is_special'] ?? false));
                return $class->load('trainer', 'membershipPlans', 'schedules');
            });

            return $this->success($this->formatClass($class), 'Fitness class created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create fitness class: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($fitnessClass)
    {
        $classId = $fitnessClass instanceof FitnessClass ? $fitnessClass->id : $fitnessClass;
        
        $class = FitnessClass::find($classId);
        if (!$class) {
            return $this->error('Fitness class not found', null, 404);
        }
        
        $class->loadCount('attendances');
        return $this->success($this->formatClass($class->load('trainer', 'schedules', 'membershipPlans')), 'Fitness class retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFitnessClassRequest $request, $fitnessClass)
    {
        try {
            // Handle both explicit ID and model injection
            $classId = $fitnessClass instanceof FitnessClass ? $fitnessClass->id : $fitnessClass;
            
            $class = FitnessClass::find($classId);
            
            if (!$class) {
                return $this->error('Fitness class not found', null, 404);
            }
            
            $data = $request->validated();
            $membershipPlanIds = $data['membership_plan_ids'] ?? null;
            unset($data['membership_plan_ids']);

            DB::transaction(function () use ($class, $data, $membershipPlanIds) {
                $class->update($data);

                if (is_array($membershipPlanIds)) {
                    $this->syncMembershipPlans($class, $membershipPlanIds, (bool) ($data['is_special'] ?? $class->is_special));
                } elseif (array_key_exists('is_special', $data)) {
                    $this->syncMembershipPlans($class, $class->membershipPlans()->pluck('membership_plans.id')->all(), (bool) $data['is_special']);
                }
            });

            return $this->success($this->formatClass($class->load('trainer', 'membershipPlans', 'schedules')), 'Fitness class updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update fitness class: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($fitnessClass)
    {
        try {
            // Handle both explicit ID and model injection
            $classId = $fitnessClass instanceof FitnessClass ? $fitnessClass->id : $fitnessClass;
            
            $class = FitnessClass::find($classId);
            
            if (!$class) {
                return $this->error('Fitness class not found', null, 404);
            }
            
            $class->delete();
            return $this->success(null, 'Fitness class deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete fitness class: ' . $e->getMessage(), null, 500);
        }
    }

    private function syncMembershipPlans(FitnessClass $class, array $membershipPlanIds, bool $isSpecial): void
    {
        $goldPlanId = MembershipPlan::where('plan_name', 'Gold')->value('id');

        if ($isSpecial) {
            if (!$goldPlanId) {
                throw new \RuntimeException('Gold membership plan does not exist');
            }

            $membershipPlanIds = [$goldPlanId];
        }

        $class->membershipPlans()->sync(array_values(array_unique(array_map('intval', $membershipPlanIds))));
        $class->is_special = $isSpecial;
        $class->save();
    }

    private function formatClass(FitnessClass $class): array
    {
        return [
            'id' => $class->id,
            'class_name' => $class->class_name,
            'description' => $class->description,
            'max_participants' => $class->max_participants,
            'difficulty_level' => $class->difficulty_level,
            'is_special' => (bool) $class->is_special,
            'trainer_id' => $class->trainer_id,
            'trainer' => $class->trainer ? [
                'id' => $class->trainer->id,
                'first_name' => $class->trainer->first_name,
                'last_name' => $class->trainer->last_name,
                'specialization' => $class->trainer->specialization,
            ] : null,
            'current_enrolled' => $class->attendances_count ?? $class->attendances()->count(),
            'remaining_slots' => max(0, $class->max_participants - ($class->attendances_count ?? $class->attendances()->count())),
            'is_full' => ($class->attendances_count ?? $class->attendances()->count()) >= $class->max_participants,
            'enrollment_percentage' => $class->max_participants > 0 ? round((($class->attendances_count ?? $class->attendances()->count()) / $class->max_participants) * 100) : 0,
            'membership_plan_ids' => $class->membershipPlans->pluck('id')->values(),
            'membership_plans' => $class->membershipPlans->map(fn ($plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'plan_name' => $plan->plan_name,
            ])->values(),
            'schedules' => $class->schedules->map(fn ($schedule) => [
                'id' => $schedule->id,
                'date' => $schedule->class_date,
                'class_time' => $schedule->start_time,
                'duration' => $schedule->duration ?? 60,
            ])->values(),
        ];
    }
}
