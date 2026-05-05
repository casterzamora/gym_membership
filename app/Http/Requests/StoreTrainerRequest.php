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
            // Trainer profile data
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'specialization' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'hourly_rate' => 'required|numeric|min:0',
            
            // Password is now set server-side to 'password', so it's optional here
            'password' => 'sometimes|string|min:8',
            
            // Optional: if user_id provided, use existing user
            'user_id' => 'sometimes|exists:users,id',
        ];
    }
}
