<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentTracking extends Model
{
    protected $table = 'equipment_tracking';
    protected $fillable = ['class_id', 'equipment_id', 'user_id', 'quantity', 'status', 'used_at', 'returned_at', 'assigned_by', 'returned_by', 'notes'];

    protected $casts = [
        'used_at' => 'datetime',
        'returned_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the fitness class this equipment is assigned to
     */
    public function fitnessClass(): BelongsTo
    {
        return $this->belongsTo(FitnessClass::class, 'class_id');
    }

    /**
     * Get the equipment being tracked
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    /**
     * Get the user using this equipment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who assigned the equipment
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get the user who returned the equipment
     */
    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    /**
     * Scope: Get only required equipment for a class
     */
    public function scopeRequired($query)
    {
        return $query->where('status', 'required')->whereNull('used_at');
    }

    /**
     * Scope: Get only equipment in use
     */
    public function scopeInUse($query)
    {
        return $query->where('status', 'in_use');
    }

    /**
     * Scope: Get only returned equipment
     */
    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }

    /**
     * Scope: Get equipment for a specific class
     */
    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope: Get equipment used in a specific session/schedule
     */
    public function scopeForSchedule($query, $scheduleId)
    {
        // Join with class_schedules to find class, then filter
        return $query->whereHas('fitnessClass.schedules', function ($q) use ($scheduleId) {
            $q->where('class_schedules.id', $scheduleId);
        });
    }

    /**
     * Scope: Get equipment tracked for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Mark equipment as in use
     */
    public function markAsInUse($userId = null, $assignedBy = null): self
    {
        $this->update([
            'status' => 'in_use',
            'used_at' => now(),
            'user_id' => $userId,
            'assigned_by' => $assignedBy,
        ]);
        return $this;
    }

    /**
     * Mark equipment as returned
     */
    public function markAsReturned($returnedBy = null): self
    {
        $this->update([
            'status' => 'returned',
            'returned_at' => now(),
            'returned_by' => $returnedBy,
        ]);
        return $this;
    }

    /**
     * Check if equipment is currently required
     */
    public function isRequired(): bool
    {
        return $this->status === 'required' && is_null($this->used_at);
    }

    /**
     * Check if equipment is currently in use
     */
    public function isInUse(): bool
    {
        return $this->status === 'in_use';
    }

    /**
     * Check if equipment has been returned
     */
    public function isReturned(): bool
    {
        return $this->status === 'returned';
    }
}
