<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipPlan extends Model
{
    use HasFactory;

    protected $fillable = ['plan_name', 'price', 'duration_months', 'description', 'max_classes_per_week'];

    protected $appends = ['name'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function getNameAttribute(): string
    {
        return $this->plan_name;
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'plan_id');
    }

    public function membershipUpgrades(): HasMany
    {
        return $this->hasMany(MembershipUpgrade::class, 'new_plan_id');
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(FitnessClass::class, 'class_memberships', 'membership_plan_id', 'class_id');
    }
}
