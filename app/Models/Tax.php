<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    protected $table = 'taxes';

    protected $fillable = [
        'name',
        'description',
        'invoice_label',
        'tax_type',
        'affectation_type_code',
        'rate_percent',
        'is_price_inclusive',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'rate_percent' => 'decimal:2',
        'is_price_inclusive' => 'boolean',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Calculate tax amount for a given price.
     */
    public function calculateTaxAmount(float $price): float
    {
        if ($this->is_price_inclusive) {
            // Tax is included: amount = price - (price / (1 + rate%))
            return $price - ($price / (1 + ($this->rate_percent / 100)));
        }

        // Tax is not included: amount = price * rate%
        return $price * ($this->rate_percent / 100);
    }

    /**
     * Calculate price with tax.
     */
    public function calculatePriceWithTax(float $basePrice): float
    {
        if ($this->is_price_inclusive) {
            return $basePrice;
        }

        return $basePrice * (1 + ($this->rate_percent / 100));
    }
}
