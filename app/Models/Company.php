<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'business_name',
        'trade_name',
        'tax_id',
        'logo',
        'is_active',
        'code',
        'phone',
        'email',
        'website',
        'address',
        'ubigeo_code',
        'country',
        'latitude',
        'longitude',
        'kitchen_printer_ip',
        'manager_id',
        'opening_time',
        'closing_time',
        'max_tables',
        'max_capacity',
        'currency',
        'timezone',
        'tax_percentage',
        'print_kitchen_ticket',
        'print_customer_receipt',
        'accept_reservations',
        'accept_delivery',
        'accept_takeout',
        'config_json',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'tax_percentage' => 'decimal:2',
        'print_kitchen_ticket' => 'boolean',
        'print_customer_receipt' => 'boolean',
        'accept_reservations' => 'boolean',
        'accept_delivery' => 'boolean',
        'accept_takeout' => 'boolean',
        'config_json' => 'array',
    ];

    protected $appends = ['ubigeo'];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the parent company (matriz).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'parent_id');
    }

    /**
     * Get child companies (branches).
     */
    public function children(): HasMany
    {
        return $this->hasMany(Company::class, 'parent_id');
    }

    /**
     * Get active branches only.
     */
    public function activeBranches(): HasMany
    {
        return $this->children()->where('is_active', true);
    }

    /**
     * Get the manager for this branch.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get all warehouses for this branch.
     */
    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class, 'branch_id');
    }

    /**
     * Get all POS terminals for this branch.
     */
    public function posTerminals(): HasMany
    {
        return $this->hasMany(PosTerminal::class, 'branch_id');
    }

    /**
     * Get all tables for this branch.
     */
    public function tables(): HasMany
    {
        return $this->hasMany(Table::class, 'branch_id');
    }

    /**
     * Get all table areas for this branch.
     */
    public function tableAreas(): HasMany
    {
        return $this->hasMany(TableArea::class, 'branch_id');
    }

    /**
     * Get all kitchen stations for this branch.
     */
    public function kitchenStations(): HasMany
    {
        return $this->hasMany(KitchenStation::class, 'branch_id');
    }

    /**
     * Get all orders for this branch.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'branch_id');
    }

    /**
     * Get all reservations for this branch.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'branch_id');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Only parent companies (matrices).
     */
    public function scopeMatrices($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope: Only branches (children).
     */
    public function scopeBranches($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Scope: Only active companies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: With branch count.
     */
    public function scopeWithBranchCount($query)
    {
        return $query->withCount(['children' => function ($q) {
            $q->where('is_active', true);
        }]);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check if this is a matriz (parent company).
     */
    public function isMatriz(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if this is a branch.
     */
    public function isBranch(): bool
    {
        return ! is_null($this->parent_id);
    }

    /**
     * Get a specific config value from config_json.
     */
    public function getConfig(string $key, mixed $default = null): mixed
    {
        return data_get($this->config_json, $key, $default);
    }

    /**
     * Set a specific config value in config_json.
     */
    public function setConfig(string $key, mixed $value): void
    {
        $config = $this->config_json ?? [];
        data_set($config, $key, $value);
        $this->config_json = $config;
        $this->save();
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Ubigeo data (will be fetched from API).
     *
     * Example structure from API:
     * [
     *   'department' => 'Lima',
     *   'province' => 'Lima',
     *   'district' => 'Miraflores',
     *   'code' => '150122'
     * ]
     */
    public function getUbigeoAttribute(): ?array
    {
        if (! $this->ubigeo_code) {
            return null;
        }

        // TODO: Implement your API call here
        // Example: return app(UbigeoService::class)->getByCode($this->ubigeo_code);

        // For now, return null or cached data
        return cache()->remember("ubigeo.{$this->ubigeo_code}", 3600, function () {
            // Your API call here
            return null;
        });
    }

    /**
     * Get full address as a single string including ubigeo data.
     */
    public function getFullAddressAttribute(): string
    {
        $ubigeo = $this->ubigeo;

        return implode(', ', array_filter([
            $this->address,
            $ubigeo['district'] ?? null,
            $ubigeo['province'] ?? null,
            $ubigeo['department'] ?? null,
            $this->country,
        ]));
    }

    /**
     * Get department from ubigeo.
     */
    public function getDepartmentAttribute(): ?string
    {
        return $this->ubigeo['department'] ?? null;
    }

    /**
     * Get province from ubigeo.
     */
    public function getProvinceAttribute(): ?string
    {
        return $this->ubigeo['province'] ?? null;
    }

    /**
     * Get district from ubigeo.
     */
    public function getDistrictAttribute(): ?string
    {
        return $this->ubigeo['district'] ?? null;
    }
}
