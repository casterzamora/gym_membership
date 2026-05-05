<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'equipment_name' => 'required|string|max:255|unique:equipment',
            'status' => 'nullable|in:Available,Maintenance,Out of Service',
            'acquisition_date' => 'nullable|date',
            'last_maintenance' => 'nullable|date',
        ];
    }
}
