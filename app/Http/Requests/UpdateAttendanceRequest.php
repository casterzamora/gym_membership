<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'check_in_time' => 'sometimes|date_format:H:i:s',
            'check_out_time' => 'sometimes|date_format:H:i:s|after:check_in_time',
        ];
    }
}
