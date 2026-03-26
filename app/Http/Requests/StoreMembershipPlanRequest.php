<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMembershipPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_name' => 'required|string|max:255|unique:membership_plans',
            'price' => 'required|numeric|min:0|max:99999.99',
            'duration_months' => 'required|integer|min:1|max:60',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'plan_name.unique' => 'A membership plan with this name already exists.',
            'price.numeric' => 'Price must be a valid number.',
            'duration_months.integer' => 'Duration must be in whole months.',
        ];
    }
}
