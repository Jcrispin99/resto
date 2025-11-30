<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_name',
        'trade_name',
        'tax_id',
        'email',
        'phone',
        'website',
        'logo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all branches for this company.
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * Get active branches only.
     */
    public function activeBranches(): HasMany
    {
        return $this->branches()->where('is_active', true);
    }

    /**
     * Scope a query to only include active companies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
