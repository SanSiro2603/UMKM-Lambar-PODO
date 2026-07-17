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
        'shipping_zone_label',
        'shipping_address',
        'shipping_phone',
        'payment_method',
        'payment_status',
        'status',
        'xendit_invoice_id',
        'xendit_invoice_url',
        'paid_at',
        'courier_name',
        'courier_phone',
        'courier_token',
        'courier_lat',
        'courier_lng',
        'courier_location_updated_at',
        'is_tracking_active',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'courier_location_updated_at' => 'datetime',
            'is_tracking_active' => 'boolean',
            'courier_lat' => 'float',
            'courier_lng' => 'float',
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
        return $this->hasOne(Transaction::class)->latestOfMany();
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

    /** URL pelacakan kurir (link sekali pakai, hanya valid selama courier_token belum dihanguskan) */
    public function courierTrackingUrl(): ?string
    {
        return $this->courier_token ? url('/lacak-kurir/' . $this->courier_token) : null;
    }
}
