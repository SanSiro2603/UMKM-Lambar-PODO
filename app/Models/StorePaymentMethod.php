<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorePaymentMethod extends Model
{
    protected $fillable = [
        'store_id',
        'type',
        'name',
        'account_name',
        'account_number',
        'qr_code',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
