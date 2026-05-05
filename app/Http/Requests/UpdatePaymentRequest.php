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
            'member_id' => 'sometimes|exists:members,id',
            'amount_paid' => 'sometimes|numeric|min:0.01|max:99999.99',
            'payment_date' => 'sometimes|date|before_or_equal:today',
            'payment_method_id' => 'sometimes|exists:payment_methods,payment_method_id',
            'coverage_start' => 'sometimes|date',
            'coverage_end' => 'sometimes|date',
        ];
    }
}
