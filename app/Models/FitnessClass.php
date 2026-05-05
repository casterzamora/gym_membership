<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FitnessClass extends Model
{
    protected $table = 'fitness_classes';
    protected $fillable = ['class_name', 'description', 'trainer_id', 'max_participants', 'difficulty_level', 'is_special'];

    protected $casts = [
        'is_special' => 'boolean',
    ];

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class, 'class_id');
    }

    public function equipment(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'class_equipment', 'class_id', 'equipment_id')
            ->withTimestamps();
    }

    public function membershipPlans(): BelongsToMany
    {
        return $this->belongsToMany(MembershipPlan::class, 'class_memberships', 'class_id', 'membership_plan_id');
    }

    public function attendances(): HasManyThrough
    {
        return $this->hasManyThrough(Attendance::class, ClassSchedule::class, 'class_id', 'schedule_id');
    }

    /**
     * Get all equipment tracking records for this class
     */
    public function tracking(): HasMany
    {
        return $this->hasMany(EquipmentTracking::class, 'class_id');
    }

    /**
     * Get required equipment for this class
     */
    public function requiredEquipment()
    {
        return $this->tracking()->required();
    }

    /**
     * Get equipment currently in use in this class
     */
    public function inUseEquipment()
    {
        return $this->tracking()->inUse();
    }
}
