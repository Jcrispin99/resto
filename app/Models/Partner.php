<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'partner_type',
        'name',
        'trade_name',
        'tax_id',
        'email',
        'phone',
        'address',
        'ubigeo_code',
        'is_customer',
        'is_supplier',
        'payment_terms_days',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_customer' => 'boolean',
        'is_supplier' => 'boolean',
        'is_active' => 'boolean',
        'payment_terms_days' => 'integer',
    ];

    /**
     * Get all purchase orders from this partner.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'partner_id');
    }

    /**
     * Scope for customers only.
     */
    public function scopeCustomers($query)
    {
        return $query->where('is_customer', true);
    }

    /**
     * Scope for suppliers only.
     */
    public function scopeSuppliers($query)
    {
        return $query->where('is_supplier', true);
    }

    /**
     * Scope for active partners.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Partner types
    const TYPE_INDIVIDUAL = 'individual';
    const TYPE_COMPANY = 'company';
    
    const DOC_DNI = 'DNI';
    const DOC_RUC = 'RUC';
    const DOC_CE = 'CE'; // Carnet de Extranjería
    const DOC_PASSPORT = 'PASSPORT';
}
