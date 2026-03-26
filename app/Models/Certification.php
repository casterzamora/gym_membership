<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Certification extends Model
{
    use HasFactory;

    protected $fillable = ['cert_name', 'issuing_organization', 'cert_number', 'issue_date', 'expiry_date'];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function trainers(): BelongsToMany
    {
        return $this->belongsToMany(Trainer::class, 'trainer_certifications')
            ->withPivot('date_obtained', 'expires_at')
            ->withTimestamps();
    }
}
