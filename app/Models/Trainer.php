<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trainer extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'first_name', 'last_name', 'specialization', 'phone', 'hourly_rate'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(FitnessClass::class);
    }

    public function certifications(): BelongsToMany
    {
        return $this->belongsToMany(Certification::class, 'trainer_certifications')
            ->withPivot('date_obtained', 'expires_at')
            ->withTimestamps();
    }
}
