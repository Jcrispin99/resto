<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'branch_id',
        'warehouse_id',
        'partner_id',
        'order_date',
        'quote_valid_until',
        'delivery_date',
        'paid_date',
        'status',
        'subtotal',
        'discount',
        'tax',
        'total',
        'delivery_address',
        'delivery_contact',
        'delivery_phone',
        'notes',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'quote_valid_until' => 'date',
        'delivery_date' => 'date',
        'paid_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the customer.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Get the branch.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the warehouse.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get items via productables.
     */
    public function items(): MorphMany
    {
        return $this->morphMany(Productable::class, 'productable');
    }

    /**
     * Get inventory movements.
     */
    public function inventoryMovements(): MorphMany
    {
        return $this->morphMany(Inventory::class, 'inventoryable');
    }

    /**
     * Scopes
     */
    public function scopeQuotes($query)
    {
        return $query->whereIn('status', ['quote', 'quote_sent']);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['approved', 'processing']);
    }

    // Status constants
    const STATUS_QUOTE = 'quote';
    const STATUS_QUOTE_SENT = 'quote_sent';
    const STATUS_APPROVED = 'approved';
    const STATUS_PROCESSING = 'processing';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';
}
