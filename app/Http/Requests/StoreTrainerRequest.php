<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:trainers',
            'phone' => 'required|string|max:20',
            'specialization' => 'required|string|max:255',
            'hourly_rate' => 'required|numeric|min:0',
        ];
    }
}
