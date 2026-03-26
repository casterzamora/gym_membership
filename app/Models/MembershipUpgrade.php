<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipUpgrade extends Model
{
    protected $fillable = ['member_id', 'old_plan_id', 'new_plan_id', 'upgrade_date'];

    protected $casts = [
        'upgrade_date' => 'date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function oldPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'old_plan_id');
    }

    public function newPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'new_plan_id');
    }
}
