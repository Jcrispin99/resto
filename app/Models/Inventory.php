<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'inventoryable_type',
        'inventoryable_id',
        'detail',
        'quantity_in',
        'cost_in',
        'total_in',
        'quantity_out',
        'cost_out',
        'total_out',
        'quantity_balance',
        'cost_balance',
        'total_balance',
    ];

    protected $casts = [
        'quantity_in' => 'decimal:3',
        'cost_in' => 'decimal:2',
        'total_in' => 'decimal:2',
        'quantity_out' => 'decimal:3',
        'cost_out' => 'decimal:2',
        'total_out' => 'decimal:2',
        'quantity_balance' => 'decimal:3',
        'cost_balance' => 'decimal:2',
        'total_balance' => 'decimal:2',
    ];

    /**
     * Get the product variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductProduct::class, 'product_id');
    }

    /**
     * Get the warehouse.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the parent inventoryable model (PurchaseOrder, Sale, etc.).
     */
    public function inventoryable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for a specific warehouse.
     */
    public function scopeForWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope for a specific product.
     */
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Get current stock balance for a product in a warehouse.
     */
    public static function getCurrentBalance(int $productId, int $warehouseId): float
    {
        return static::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->latest()
            ->value('quantity_balance') ?? 0;
    }
}
