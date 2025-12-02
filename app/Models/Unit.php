<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'abbreviation',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all product templates using this unit.
     */
    public function productTemplates(): HasMany
    {
        return $this->hasMany(ProductTemplate::class, 'unit_id');
    }

    /**
     * Scope for active units.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Unit types
    const TYPE_WEIGHT = 'weight';

    const TYPE_VOLUME = 'volume';

    const TYPE_LENGTH = 'length';

    const TYPE_UNIT = 'unit';
}
