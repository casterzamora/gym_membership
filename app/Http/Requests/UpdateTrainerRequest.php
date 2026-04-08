<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTrainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:trainers,email,' . $this->trainer->id,
            'phone' => 'sometimes|string|max:20',
            'specialization' => 'sometimes|string|max:255',
            'hourly_rate' => 'sometimes|numeric|min:0',
        ];
    }
}
