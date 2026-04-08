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
            'attendance_status' => 'sometimes|string|in:Present,Absent,Late',
            'attendance_notes' => 'nullable|string|max:500',
            'recorded_at' => 'nullable|datetime',
        ];
    }
}
