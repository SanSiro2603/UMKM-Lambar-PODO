<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Rating;

class Store extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'address',
        'bank_name',
        'bank_code',
        'bank_account_no',
        'bank_account_name',
        'bank_verify_status',
        'bank_reject_reason',
        'ktp_photo',
        'logo',
        'banner',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(StorePaymentMethod::class);
    }

    /** Rating toko = rata-rata rating semua produk di toko ini */
    public function getAvgRatingAttribute(): float
    {
        return round(
            Rating::whereIn('product_id', $this->products()->pluck('id'))->avg('rating') ?? 0,
            1
        );
    }

    public function getRatingCountAttribute(): int
    {
        return Rating::whereIn('product_id', $this->products()->pluck('id'))->count();
    }

    /** Cek apakah seller sudah mengisi data rekening */
    public function hasBankAccount(): bool
    {
        return ! empty($this->bank_code)
            && ! empty($this->bank_account_no)
            && ! empty($this->bank_account_name);
    }

    /** Cek apakah rekening bank seller sudah terverifikasi admin */
    public function isBankVerified(): bool
    {
        return $this->bank_verify_status === 'verified';
    }

    /** Scope: seller yang rekeningnya sudah verified */
    public function scopeBankVerified(Builder $query): Builder
    {
        return $query->where('bank_verify_status', 'verified');
    }
}