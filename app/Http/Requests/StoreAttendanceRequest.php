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
            'class_schedule_id' => 'required|exists:class_schedules,id|unique:attendance,class_schedule_id,NULL,id,member_id,' . $this->member_id,
            'check_in_time' => 'nullable|date_format:H:i:s',
            'check_out_time' => 'nullable|date_format:H:i:s',
        ];
    }

    public function messages(): array
    {
        return [
            'class_schedule_id.unique' => 'This member is already registered for this class.',
        ];
    }
}
