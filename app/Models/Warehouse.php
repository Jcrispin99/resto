<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'code',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the branch this warehouse belongs to.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get all inventory movements (kardex) for this warehouse.
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Get purchase orders to this warehouse.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get sale orders from this warehouse.
     */
    public function saleOrders(): HasMany
    {
        return $this->hasMany(SaleOrder::class);
    }

    /**
     * Get transfers from this warehouse.
     */
    public function transfersFrom(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'from_warehouse_id');
    }

    /**
     * Get transfers to this warehouse.
     */
    public function transfersTo(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'to_warehouse_id');
    }

    /**
     * Scope for active warehouses.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get total value of inventory in this warehouse.
     */
    public function getTotalInventoryValue(): float
    {
        return $this->inventories()
            ->selectRaw('SUM(total_balance) as total')
            ->value('total') ?? 0;
    }

    /**
     * Get stock of a specific product in this warehouse.
     */
    public function getProductStock(int $productId): float
    {
        return $this->inventories()
            ->where('product_id', $productId)
            ->selectRaw('SUM(quantity_balance) as total')
            ->value('total') ?? 0;
    }
}
