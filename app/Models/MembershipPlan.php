<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipPlan extends Model
{
    use HasFactory;

    protected $fillable = ['plan_name', 'price', 'duration_months', 'description'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'plan_id');
    }

    public function membershipUpgrades(): HasMany
    {
        return $this->hasMany(MembershipUpgrade::class, 'new_plan_id');
    }
}
