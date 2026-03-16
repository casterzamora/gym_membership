<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'check_in_time',
        'check_out_time',
        'date',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function getDurationAttribute()
    {
        if ($this->check_out_time && $this->check_in_time) {
            return $this->check_out_time->diffInMinutes($this->check_in_time);
        }
        return null;
    }
}
