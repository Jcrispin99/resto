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
     * Get stock entries for this variant.
     */
    public function stock(): HasMany
    {
        return $this->hasMany(Stock::class, 'product_id');
    }

    /**
     * Get stock movements for this variant.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'product_id');
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
     * Get total stock across all warehouses.
     */
    public function getTotalStockAttribute(): float
    {
        return $this->stock()->sum('quantity');
    }

    /**
     * Get profit margin percentage.
     */
    public function getProfitMarginAttribute(): ?float
    {
        if (!$this->sale_price || !$this->cost_price) {
            return null;
        }

        return (($this->sale_price - $this->cost_price) / $this->sale_price) * 100;
    }
}
