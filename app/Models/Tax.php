<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

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

    /**
     * Scope for active taxes only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default tax.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true)->where('is_active', true);
    }

    /**
     * Scope for specific tax type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('tax_type', $type);
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

    /**
     * Get tax rate as decimal (for calculations).
     */
    public function getRateDecimalAttribute(): float
    {
        return $this->rate_percent / 100;
    }

    /**
     * Common SUNAT affectation codes.
     */
    const AFFECTATION_GRAVADO = '10'; // Gravado - Operación Onerosa
    const AFFECTATION_EXONERADO = '20'; // Exonerado
    const AFFECTATION_INAFECTO = '30'; // Inafecto
    const AFFECTATION_EXPORTACION = '40'; // Exportación
    const AFFECTATION_GRATUITO = '11'; // Gravado - Retiro por premio
    const AFFECTATION_GRATUITO_PROMO = '12'; // Gravado - Retiro por donación
    const AFFECTATION_GRATUITO_PUBLICIDAD = '13'; // Gravado - Retiro por publicidad

    /**
     * Common tax types.
     */
    const TYPE_IGV = 'IGV';
    const TYPE_ICBPER = 'ICBPER';
    const TYPE_ISC = 'ISC';
    const TYPE_RETENCION = 'RETENCION';
    const TYPE_PERCEPCION = 'PERCEPCION';
}
