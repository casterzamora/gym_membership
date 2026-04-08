<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => 'required|exists:members,id',
            'amount_paid' => 'required|numeric|min:0.01|max:99999.99',
            'payment_date' => 'required|date',
            'payment_method_id' => 'required|exists:payment_methods,payment_method_id',
            'coverage_start' => 'required|date',
            'coverage_end' => 'required|date|after:coverage_start',
        ];
    }
}
