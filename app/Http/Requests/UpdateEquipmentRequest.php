<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'equipment_name' => 'sometimes|string|max:255|unique:equipment,equipment_name,' . $this->equipment->id,
            'quantity' => 'sometimes|integer|min:1|max:1000',
            'purchase_date' => 'nullable|date',
            'condition' => 'sometimes|in:new,good,fair,poor',
        ];
    }
}
