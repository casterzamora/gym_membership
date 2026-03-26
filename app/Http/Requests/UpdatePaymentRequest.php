<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'sometimes|numeric|min:0.01|max:99999.99',
            'payment_date' => 'sometimes|date',
            'payment_method' => 'sometimes|in:cash,card,transfer,check',
            'coverage_start' => 'sometimes|date',
            'coverage_end' => 'sometimes|date|after:coverage_start',
        ];
    }
}
