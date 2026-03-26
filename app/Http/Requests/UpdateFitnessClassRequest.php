<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFitnessClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_name' => 'sometimes|string|max:255|unique:fitness_classes,class_name,' . $this->fitness_class->id,
            'description' => 'nullable|string|max:1000',
            'capacity' => 'sometimes|integer|min:1|max:100',
            'trainer_id' => 'sometimes|exists:trainers,id',
        ];
    }
}
