<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'journal_id',
        'branch_id',
        'warehouse_id',
        'partner_id',
        'order_date',
        'expected_delivery_date',
        'received_date',
        'paid_date',
        'status',
        'subtotal',
        'tax',
        'total',
        'notes',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'received_date' => 'date',
        'paid_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the partner (supplier).
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Get the journal for fiscal numbering.
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * Get the branch (company).
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'branch_id');
    }

    /**
     * Get the warehouse.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get items via productables (polymorphic).
     */
    public function productables()
    {
        return $this->morphMany(Productable::class, 'productable');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // Status constants
    const STATUS_QUOTE_REQUEST = 'quote_request';

    const STATUS_QUOTE_RECEIVED = 'quote_received';

    const STATUS_ORDERED = 'ordered';

    const STATUS_APPROVED = 'approved';

    const STATUS_RECEIVED = 'received';

    const STATUS_PAID = 'paid';

    const STATUS_CANCELLED = 'cancelled';
}
