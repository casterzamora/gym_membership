<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'attendance';
    public $incrementing = false;
    public $timestamps = true;
    protected $primaryKey = ['member_id', 'schedule_id'];

    protected $fillable = ['member_id', 'schedule_id', 'attendance_status', 'attendance_notes', 'recorded_at'];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ClassSchedule::class, 'schedule_id');
    }
}
