<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 'member_id', 'amount_paid', 'payment_date', 'payment_method_id',
        'coverage_start', 'coverage_end',
        'payment_status', 'checkout_full_name', 'checkout_email',
        'payment_reference', 'card_brand', 'card_last4',
        'billing_address_line1', 'billing_address_line2', 'billing_city',
        'billing_state', 'billing_postal_code', 'billing_country',
        'payment_failure_reason'
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'payment_date' => 'date',
        'coverage_start' => 'date',
        'coverage_end' => 'date',
    ];

    /**
     * Get the user who made the payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the member associated with this payment
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    /**
     * Get the payment method used
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
}
