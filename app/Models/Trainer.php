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

    protected $table = 'trainers';
    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'first_name', 'last_name', 'specialization', 'phone', 'hourly_rate'];

    protected $appends = ['email'];

    /**
     * Get the user associated with this trainer
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get email from the associated user
     */
    public function getEmailAttribute()
    {
        return $this->user?->email ?? '';
    }

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
