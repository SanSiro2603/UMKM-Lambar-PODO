<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'order_id',
        'seller_id',
        'total_amount',
        'platform_fee',
        'seller_amount',
        'xendit_invoice_id',
        'xendit_payment_method',
        'xendit_payment_channel',
        'xendit_invoice_url',
        'xendit_disbursement_id',
        'status',
        'paid_at',
        'expired_at',
        'disbursed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'paid_at'       => 'datetime',
            'expired_at'    => 'datetime',
            'disbursed_at'  => 'datetime',
            'metadata'      => 'array',
            'total_amount'  => 'integer',
            'platform_fee'  => 'integer',
            'seller_amount' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isDisbursed(): bool
    {
        return $this->status === 'disbursed';
    }
}