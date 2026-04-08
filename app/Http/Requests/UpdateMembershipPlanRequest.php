<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMembershipPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_name' => 'sometimes|string|max:255|unique:membership_plans,plan_name,' . $this->plan->id,
            'price' => 'sometimes|numeric|min:0|max:99999.99',
            'duration_months' => 'sometimes|integer|min:1|max:60',
            'description' => 'nullable|string|max:1000',
            'max_classes_per_week' => 'nullable|integer|min:1|max:50',
        ];
    }
}
