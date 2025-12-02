<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'value',
    ];

    /**
     * Get the attribute this value belongs to.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id');
    }

    /**
     * Get full display name (attribute: value).
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->attribute->name}: {$this->value}";
    }
}
