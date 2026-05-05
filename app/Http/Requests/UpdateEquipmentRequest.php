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
            'status' => 'sometimes|in:Available,Maintenance,Out of Service',
            'acquisition_date' => 'nullable|date',
            'last_maintenance' => 'nullable|date',
        ];
    }
}
