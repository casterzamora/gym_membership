<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    protected $table = 'equipment';
    protected $fillable = ['equipment_name', 'status', 'acquisition_date', 'last_maintenance'];

    protected $casts = [
        'acquisition_date' => 'date',
        'last_maintenance' => 'date',
    ];

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(FitnessClass::class, 'class_equipment', 'equipment_id', 'class_id')
            ->withTimestamps();
    }

    public function usage(): HasMany
    {
        return $this->hasMany(EquipmentUsage::class);
    }
}
