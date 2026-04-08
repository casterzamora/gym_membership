<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Trainer extends Model
{
    use HasFactory;

    protected $table = 'trainers';
    protected $primaryKey = 'id';

    protected $fillable = ['first_name', 'last_name', 'email', 'specialization', 'phone', 'hourly_rate'];

    public function classes(): HasMany
    {
        return $this->hasMany(FitnessClass::class, 'trainer_id');
    }

    public function certifications(): BelongsToMany
    {
        return $this->belongsToMany(Certification::class, 'trainer_certifications')
            ->withPivot('date_obtained')
            ->withTimestamps();
    }
}
