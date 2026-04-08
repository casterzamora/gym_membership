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
        $classId = $this->route('fitnessClass')->id ?? null;
        
        return [
            'class_name' => 'sometimes|string|max:255|unique:fitness_classes,class_name,' . $classId,
            'description' => 'sometimes|string|max:1000',
            'max_participants' => 'sometimes|integer|min:1|max:100',
            'trainer_id' => 'sometimes|exists:trainers,id',
            'difficulty_level' => 'sometimes|string|max:50',
        ];
    }
}
