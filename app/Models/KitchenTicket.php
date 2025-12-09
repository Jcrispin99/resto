<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KitchenTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'order_id',
        'station_id',
        'priority',
        'status',
        'printed_at',
        'started_at',
        'completed_at',
        'delivered_at',
        'prepared_by',
    ];

    protected $casts = [
        'printed_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(KitchenStation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(KitchenTicketItem::class, 'ticket_id');
    }

    public function chef(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY = 'ready';
    const STATUS_COMPLETED = 'completed';
    const STATUS_DELIVERED = 'delivered';
}
