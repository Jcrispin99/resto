<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductTemplateAttributeLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_template_id',
        'attribute_id',
    ];

    /**
     * Get the template.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ProductTemplate::class, 'product_template_id');
    }

    /**
     * Get the attribute.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id');
    }

    /**
     * Get the allowed values for this attribute on this template.
     */
    public function values(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductAttributeValue::class,
            'product_template_attribute_line_values',
            'product_template_attribute_line_id',
            'product_attribute_value_id'
        );
    }
}
