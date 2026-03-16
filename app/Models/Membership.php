<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'package_id',
        'start_date',
        'end_date',
        'duration_months',
        'price',
        'status', // active, expired, cancelled
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function isExpired()
    {
        return now()->isAfter($this->end_date);
    }

    public function isActive()
    {
        return $this->status === 'active' && !$this->isExpired();
    }
}
