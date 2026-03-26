<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSchedule extends Model
{
    protected $table = 'class_schedules';
    protected $fillable = ['class_id', 'class_date', 'start_time', 'end_time'];

    protected $casts = [
        'class_date' => 'date',
    ];

    public function fitnessClass(): BelongsTo
    {
        return $this->belongsTo(FitnessClass::class, 'class_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'schedule_id');
    }

    public function equipmentUsage(): HasMany
    {
        return $this->hasMany(EquipmentUsage::class, 'schedule_id');
    }
}
