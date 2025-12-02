<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_template_id',
        'ingredient_id',
        'quantity',
        'unit_id',
        'waste_percentage',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'waste_percentage' => 'decimal:2',
    ];

    /**
     * Get the dish/product that uses this recipe.
     */
    public function productTemplate(): BelongsTo
    {
        return $this->belongsTo(ProductTemplate::class, 'product_template_id');
    }

    /**
     * Get the ingredient (product variant).
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(ProductProduct::class, 'ingredient_id');
    }

    /**
     * Get the unit of measure.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
