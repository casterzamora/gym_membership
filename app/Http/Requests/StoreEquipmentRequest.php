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
            'quantity' => 'required|integer|min:1|max:1000',
            'purchase_date' => 'nullable|date',
            'condition' => 'required|in:new,good,fair,poor',
        ];
    }
}
