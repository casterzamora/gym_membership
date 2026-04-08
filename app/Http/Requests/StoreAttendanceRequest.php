<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => 'required|exists:members,id',
            'schedule_id' => 'required|exists:class_schedules,id',
            'attendance_status' => 'required|string|in:Present,Absent,Late',
            'attendance_notes' => 'nullable|string|max:500',
            'recorded_at' => 'nullable|datetime',
        ];
    }
}
