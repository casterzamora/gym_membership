<?php

namespace App\Http\Requests;

use App\Models\MembershipPlan;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFitnessClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $classId = $this->route('fitnessClass')->id ?? null;
        
        return [
            'class_name' => 'sometimes|string|max:255|unique:fitness_classes,class_name,' . $classId,
            'description' => 'sometimes|string|max:1000',
            'max_participants' => 'sometimes|integer|min:1|max:100',
            'trainer_id' => 'sometimes|exists:trainers,id',
            'difficulty_level' => 'sometimes|string|max:50',
            'is_special' => 'nullable|boolean',
            'membership_plan_ids' => 'sometimes|array|min:1',
            'membership_plan_ids.*' => 'exists:membership_plans,id',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->has('is_special') && !$this->has('membership_plan_ids')) {
                return;
            }

            $membershipPlanIds = collect($this->input('membership_plan_ids', []))->map(fn ($id) => (int) $id)->values();
            $goldPlanId = MembershipPlan::where('plan_name', 'Gold')->value('id');

            if ($this->boolean('is_special')) {
                if (!$goldPlanId) {
                    $validator->errors()->add('is_special', 'Gold membership plan must exist before creating a special class.');
                    return;
                }

                if ($membershipPlanIds->count() !== 1 || !$membershipPlanIds->contains((int) $goldPlanId)) {
                    $validator->errors()->add('membership_plan_ids', 'Special classes can only be assigned to the Gold membership plan.');
                }
            }
        });
    }
}
