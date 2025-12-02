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

    protected $table = 'product_template';

    protected $fillable = [
        'category_id',
        'menu_category_id',
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
        'is_active',
    ];

    protected $casts = [
        'can_be_sold' => 'boolean',
        'can_be_purchased' => 'boolean',
        'can_be_stocked' => 'boolean',
        'sale_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the inventory category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    /**
     * Get the menu category (for POS display).
     */
    public function menuCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'menu_category_id');
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
     * Get menu settings (1:1).
     */
    public function menuSettings()
    {
        return $this->hasOne(ProductMenuSettings::class, 'product_template_id');
    }

    /**
     * Get images (polymorphic).
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Imageable::class, 'imageable');
    }

    /**
     * Get recipes (ingredients for this dish).
     */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class, 'product_template_id');
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
    const TYPE_COMBO = 'combo';
}
