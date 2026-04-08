<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFitnessClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_name' => 'required|string|max:255|unique:fitness_classes',
            'description' => 'nullable|string|max:1000',
            'max_participants' => 'required|integer|min:1|max:100',
            'trainer_id' => 'required|exists:trainers,id',
            'difficulty_level' => 'nullable|string|max:50',
        ];
    }
}
