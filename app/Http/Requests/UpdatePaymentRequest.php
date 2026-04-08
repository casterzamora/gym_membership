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
            'amount_paid' => 'sometimes|numeric|min:0.01|max:99999.99',
            'payment_date' => 'sometimes|date',
            'payment_method_id' => 'sometimes|exists:payment_methods,payment_method_id',
            'coverage_start' => 'sometimes|date',
            'coverage_end' => 'sometimes|date|after:coverage_start',
        ];
    }
}
