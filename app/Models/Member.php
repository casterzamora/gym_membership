<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'date_of_birth',
        'address',
        'membership_id',
        'join_date',
        'status', // active, inactive, suspended
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'join_date' => 'date',
    ];

    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
