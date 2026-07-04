<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_code',
        'customer_id',
        'store_id',
        'total_price',
        'shipping_cost',
        'shipping_address',
        'payment_method',
        'payment_status',
        'status',
        'xendit_invoice_id',
        'xendit_invoice_url',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transaction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    /** Apakah order sudah bisa dibayar via Xendit */
    public function canPay(): bool
    {
        return $this->payment_status === 'unpaid'
            && $this->status === 'waiting_payment'
            && $this->total_price > 0;
    }

    /** Apakah order sudah dibayar */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }
}