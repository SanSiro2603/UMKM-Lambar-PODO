<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image',
        'rating',
        'sold',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function soldOrderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class)
            ->whereHas('order', function (Builder $query) {
                $query->where('status', '!=', 'cancelled');
            });
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    /** Rata-rata rating dengan 1 desimal */
    public function getAvgRatingAttribute(): float
    {
        return round($this->ratings()->avg('rating') ?? 0, 1);
    }

    /** Jumlah total rating/review */
    public function getRatingCountAttribute(): int
    {
        return $this->ratings()->count();
    }

    public function scopeWithSoldQuantity(Builder $query): Builder
    {
        return $query->withSum('soldOrderItems as sold_quantity', 'qty');
    }

    public function getSoldQuantityAttribute($value): int
    {
        if ($value !== null) {
            return (int) $value;
        }

        if ($this->relationLoaded('soldOrderItems')) {
            return (int) $this->soldOrderItems->sum('qty');
        }

        return (int) $this->soldOrderItems()->sum('qty');
    }
}
