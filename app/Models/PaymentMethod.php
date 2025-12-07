<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'requires_reference',
        'is_active',
    ];

    protected $casts = [
        'requires_reference' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Payment types
    const TYPE_CASH = 'cash';
    const TYPE_CARD = 'card';
    const TYPE_TRANSFER = 'transfer';
    const TYPE_QR = 'qr';
    const TYPE_WALLET = 'wallet';
}
