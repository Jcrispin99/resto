<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComboItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'combo_id',
        'product_template_id',
        'quantity',
        'allow_substitution',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'allow_substitution' => 'boolean',
    ];

    /**
     * Get the combo this item belongs to.
     */
    public function combo(): BelongsTo
    {
        return $this->belongsTo(Combo::class);
    }

    /**
     * Get the product template for this combo item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductTemplate::class, 'product_template_id');
    }
}
