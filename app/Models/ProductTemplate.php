<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'unit_id',
        'name',
        'description',
        'internal_reference',
        'barcode',
        'product_type',
        'can_be_sold',
        'can_be_purchased',
        'can_be_stocked',
        'sale_price',
        'cost_price',
        'is_active',
    ];

    protected $casts = [
        'can_be_sold' => 'boolean',
        'can_be_purchased' => 'boolean',
        'can_be_stocked' => 'boolean',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    /**
     * Get the unit of measure.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get all product variants.
     */
    public function products(): HasMany
    {
        return $this->hasMany(ProductProduct::class, 'template_id');
    }

    /**
     * Get active product variants only.
     */
    public function activeProducts(): HasMany
    {
        return $this->products()->where('is_active', true);
    }

    /**
     * Get template attributes (configurable attributes).
     */
    public function templateAttributes(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductAttribute::class,
            'product_template_attributes',
            'template_id',
            'attribute_id'
        );
    }

    /**
     * Get images (polymorphic).
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Imageable::class, 'imageable');
    }

    /**
     * Scope for active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for sellable products.
     */
    public function scopeSellable($query)
    {
        return $query->where('can_be_sold', true);
    }

    /**
     * Scope for purchasable products.
     */
    public function scopePurchasable($query)
    {
        return $query->where('can_be_purchased', true);
    }

    /**
     * Scope for stockable products.
     */
    public function scopeStockable($query)
    {
        return $query->where('can_be_stocked', true);
    }

    // Product types
    const TYPE_CONSUMABLE = 'consumable';
    const TYPE_STORABLE = 'storable';
    const TYPE_SERVICE = 'service';
}
