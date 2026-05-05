<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'plan_id' => 'nullable|exists:membership_plans,id',
            'fitness_goal' => 'nullable|string|max:255',
            'health_notes' => 'nullable|string',
            'registration_type' => 'nullable|string|default:standard',
            'membership_start' => 'nullable|date',
            'membership_end' => 'nullable|date|after:membership_start',
            'membership_status' => 'nullable|string|in:active,suspended,cancelled',
        ];
    }
}
