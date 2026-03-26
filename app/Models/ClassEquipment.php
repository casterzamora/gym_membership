<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassEquipment extends Model
{
    protected $table = 'class_equipment';
    public $timestamps = true;
    public $incrementing = false;

    protected $fillable = ['class_id', 'equipment_id'];

    public function fitnessClass(): BelongsTo
    {
        return $this->belongsTo(FitnessClass::class, 'class_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}
