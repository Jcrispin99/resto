<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'type',
        'name',
        'full_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Boot method to auto-update full_name.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($category) {
            $category->updateFullName();
        });

        static::saved(function ($category) {
            // Update children's full_name when parent changes
            if ($category->children()->exists()) {
                foreach ($category->children as $child) {
                    $child->updateFullName();
                    $child->saveQuietly();
                }
            }
        });
    }

    /**
     * Update the full_name field.
     */
    public function updateFullName(): void
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        $this->full_name = implode(' / ', $path);
    }

    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    /**
     * Get child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    /**
     * Get all product templates in this category.
     */
    public function productTemplates(): HasMany
    {
        return $this->hasMany(ProductTemplate::class, 'category_id');
    }

    /**
     * Scope for active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for root categories (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for inventory categories.
     */
    public function scopeInventory($query)
    {
        return $query->where('type', 'inventory');
    }

    /**
     * Scope for menu/POS categories.
     */
    public function scopeMenu($query)
    {
        return $query->where('type', 'menu');
    }
}
