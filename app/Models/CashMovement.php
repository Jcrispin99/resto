<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $dates = ['created_at'];

    protected $fillable = [
        'cash_register_id',
        'type',
        'concept',
        'amount',
        'payment_method',
        'reference',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Movement types
    const TYPE_INCOME = 'income';

    const TYPE_EXPENSE = 'expense';

    const TYPE_OPENING = 'opening';

    const TYPE_CLOSING = 'closing';

    const TYPE_DEPOSIT = 'deposit';

    const TYPE_WITHDRAWAL = 'withdrawal';
}
