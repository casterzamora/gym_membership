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
    protected $fillable = ['class_name', 'description', 'trainer_id', 'max_participants', 'difficulty_level'];

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

    public function attendances(): HasManyThrough
    {
        return $this->hasManyThrough(Attendance::class, ClassSchedule::class, 'class_id', 'schedule_id');
    }
}
