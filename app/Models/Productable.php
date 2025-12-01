<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Productable extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'productable_type',
        'productable_id',
        'quantity',
        'unit_price',
        'discount',
        'tax_amount',
        'total',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the parent productable model (order, purchase, recipe, etc.).
     */
    public function productable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the product variant.
     */
    public function product()
    {
        return $this->belongsTo(ProductProduct::class, 'product_id');
    }
}
