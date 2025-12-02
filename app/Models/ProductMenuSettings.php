<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductMenuSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_template_id',
        'menu_name',
        'menu_description',
        'preparation_time_minutes',
        'is_featured',
        'display_order',
        'calories',
        'is_spicy',
        'is_vegetarian',
        'is_vegan',
        'is_gluten_free',
        'allergens',
        'available_for_dine_in',
        'available_for_takeout',
        'available_for_delivery',
    ];

    protected $casts = [
        'preparation_time_minutes' => 'integer',
        'is_featured' => 'boolean',
        'display_order' => 'integer',
        'calories' => 'integer',
        'is_spicy' => 'boolean',
        'is_vegetarian' => 'boolean',
        'is_vegan' => 'boolean',
        'is_gluten_free' => 'boolean',
        'allergens' => 'array',
        'available_for_dine_in' => 'boolean',
        'available_for_takeout' => 'boolean',
        'available_for_delivery' => 'boolean',
    ];

    /**
     * Get the product template.
     */
    public function productTemplate(): BelongsTo
    {
        return $this->belongsTo(ProductTemplate::class, 'product_template_id');
    }
}
