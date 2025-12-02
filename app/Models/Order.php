<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'branch_id',
        'cash_register_id',
        'table_id',
        'partner_id',
        'order_type',
        'status',
        'payment_status',
        'order_date',
        'scheduled_time',
        'served_time',
        'completed_time',
        'paid_at',
        'waiter_id',
        'cashier_id',
        'guests_count',
        'subtotal',
        'discount',
        'tax',
        'service_charge',
        'delivery_fee',
        'tip_amount',
        'total',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'scheduled_time' => 'datetime',
        'served_time' => 'datetime',
        'completed_time' => 'datetime',
        'paid_at' => 'datetime',
        'guests_count' => 'integer',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'tip_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function waiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waiter_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function inventoryMovements(): MorphMany
    {
        return $this->morphMany(Inventory::class, 'inventoryable');
    }

    // Order types
    const TYPE_DINE_IN = 'dine_in';
    const TYPE_TAKEOUT = 'takeout';
    const TYPE_DELIVERY = 'delivery';
    const TYPE_DIGITAL_MENU = 'digital_menu';

    // Status
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY = 'ready';
    const STATUS_SERVED = 'served';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Payment status
    const PAYMENT_UNPAID = 'unpaid';
    const PAYMENT_PARTIAL = 'partial';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_REFUNDED = 'refunded';
}
