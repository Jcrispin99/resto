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
        'partner_type',
        'name',
        'tax_id',
        'document_type',
        'document_number',
        'email',
        'phone',
        'website',
        'is_customer',
        'is_supplier',
        'credit_limit',
        'payment_terms',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_customer' => 'boolean',
        'is_supplier' => 'boolean',
        'credit_limit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get partner contacts.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(PartnerContact::class);
    }

    /**
     * Get partner addresses.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(PartnerAddress::class);
    }

    /**
     * Get purchase orders.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get sales orders (as customer).
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get images.
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Imageable::class, 'imageable');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCustomers($query)
    {
        return $query->where('is_customer', true);
    }

    public function scopeSuppliers($query)
    {
        return $query->where('is_supplier', true);
    }

    // Partner & Document types
    const TYPE_COMPANY = 'company';
    const TYPE_PERSON = 'person';
    
    const DOC_DNI = 'DNI';
    const DOC_RUC = 'RUC';
    const DOC_CE = 'CE'; // Carnet de Extranjería
    const DOC_PASSPORT = 'PASSPORT';
}
