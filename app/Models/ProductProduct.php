<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_product';

    protected $fillable = [
        'template_id',
        'sku',
        'barcode',
        'sale_price',
        'is_active',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the template.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ProductTemplate::class, 'template_id');
    }

    /**
     * Get variant attribute values (Talla=M, Color=Rojo).
     */
    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductAttributeValue::class,
            'attribute_value_product',
            'product_id',
            'attribute_value_id'
        );
    }

    /**
     * Get inventory movements (kardex) for this variant.
     * This is the SOURCE OF TRUTH for stock levels.
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'product_id');
    }

    /**
     * Get polymorphic relations (orders, purchases, recipes, etc.).
     */
    public function productables(): MorphMany
    {
        return $this->morphMany(Productable::class, 'productable');
    }

    /**
     * Get images (polymorphic).
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Imageable::class, 'imageable');
    }

    /**
     * Scope for active variants.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get auto-generated name (Template + Attributes).
     * Example: "Polo Deportivo (Rojo, M)"
     */
    public function getNameAttribute(): string
    {
        if ($this->attributeValues->isEmpty()) {
            return $this->template->name;
        }

        $attributes = $this->attributeValues->pluck('value')->implode(', ');

        return "{$this->template->name} ({$attributes})";
    }

    /**
     * Get total stock across all warehouses (from kardex balance).
     */
    public function getTotalStockAttribute(): float
    {
        return $this->inventories()
            ->selectRaw('SUM(quantity_balance) as total')
            ->value('total') ?? 0;
    }

    /**
     * Get stock for a specific warehouse.
     */
    public function getStockInWarehouse(int $warehouseId): float
    {
        return $this->inventories()
            ->where('warehouse_id', $warehouseId)
            ->selectRaw('SUM(quantity_balance) as total')
            ->value('total') ?? 0;
    }

    /**
     * Get profit margin percentage.
     */
    public function getProfitMarginAttribute(): ?float
    {
        // Note: cost_price should come from average cost in kardex
        $avgCost = $this->inventories()
            ->where('quantity_balance', '>', 0)
            ->avg('cost_balance');

        if (! $this->sale_price || ! $avgCost) {
            return null;
        }

        return (($this->sale_price - $avgCost) / $this->sale_price) * 100;
    }
}
