<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Get all values for this attribute.
     */
    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id');
    }

    /**
     * Get active values only.
     */
    public function activeValues(): HasMany
    {
        return $this->values()->where('is_active', true);
    }
}
