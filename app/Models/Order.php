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
        'journal_id',
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

    /**
     * Get the kitchen tickets for this order.
     */
    public function kitchenTickets(): HasMany
    {
        return $this->hasMany(KitchenTicket::class);
    }

    /**
     * Generate kitchen tickets based on order items and their stations.
     * Only generates tickets for items that don't already have one.
     */
    public function generateKitchenTickets(): void
    {
        // Load items with their product template, station and existing ticket items
        $this->loadMissing('items.productTemplate.kitchenStation', 'kitchenTickets.items');

        // Get IDs of items that already have a kitchen ticket
        $existingItemIds = $this->kitchenTickets
            ->flatMap(fn ($ticket) => $ticket->items->pluck('order_item_id'))
            ->unique()
            ->toArray();

        // Filter to only new items (without a ticket)
        $newItems = $this->items->filter(function ($item) use ($existingItemIds) {
            return !in_array($item->id, $existingItemIds);
        });

        if ($newItems->isEmpty()) {
            return; // No new items to process
        }

        // Group new items by station ID
        $itemsByStation = $newItems->groupBy(function ($item) {
            return $item->productTemplate->kitchen_station_id ?? 'no_station';
        });

        foreach ($itemsByStation as $stationId => $items) {
            if ($stationId === 'no_station') {
                continue; // Skip items without a station
            }

            // Always create a NEW ticket for new items (ensures it gets printed)
            $ticketNumber = $this->order_number . '-' . $stationId . '-' . now()->timestamp;
            
            $ticket = $this->kitchenTickets()->create([
                'ticket_number' => $ticketNumber,
                'station_id' => $stationId,
                'status' => KitchenTicket::STATUS_PENDING,
                'priority' => 'normal',
            ]);

            // Add new items to the ticket
            foreach ($items as $item) {
                $ticket->items()->create([
                    'order_item_id' => $item->id,
                    'quantity' => $item->quantity,
                    'status' => 'pending',
                ]);
            }
        }
    }

    /**
     * Get the table this order belongs to.
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

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
