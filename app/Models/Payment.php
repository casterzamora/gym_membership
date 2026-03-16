<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'membership_id',
        'amount',
        'payment_date',
        'payment_method', // cash, card, transfer
        'status', // pending, completed, failed
        'transaction_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }
}
