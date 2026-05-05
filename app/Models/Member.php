<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Member extends Model
{
    use HasFactory, HasApiTokens;

    protected $table = 'members';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'first_name', 'last_name', 'phone', 'date_of_birth',
        'fitness_goal', 'health_notes',
        'registration_type', 'plan_id', 'membership_start', 'membership_end',
        'membership_status'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'membership_start' => 'date',
        'membership_end' => 'date',
    ];

    protected $hidden = ['password_hash'];

    /**
     * Get the user associated with this member
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'plan_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'member_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'member_id');
    }

    public function membershipUpgrades(): HasMany
    {
        return $this->hasMany(MembershipUpgrade::class, 'member_id');
    }
}
