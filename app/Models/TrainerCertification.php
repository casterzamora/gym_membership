<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainerCertification extends Model
{
    protected $table = 'trainer_certifications';
    public $timestamps = true;
    public $incrementing = false;

    protected $fillable = ['trainer_id', 'certification_id', 'date_obtained', 'expires_at'];

    protected $casts = [
        'date_obtained' => 'date',
        'expires_at' => 'date',
    ];

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }
}
