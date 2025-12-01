<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'business_name',
        'tax_id',
        'address',
        'ubigeo_code',
        'country',
        'latitude',
        'longitude',
        'phone',
        'email',
        'website',
        'manager_id',
        'opening_time',
        'closing_time',
        'max_tables',
        'max_capacity',
        'is_active',
        // Settings fields (previously in branch_settings table)
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
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        // Settings casts
        'tax_percentage' => 'decimal:2',
        'print_kitchen_ticket' => 'boolean',
        'print_customer_receipt' => 'boolean',
        'accept_reservations' => 'boolean',
        'accept_delivery' => 'boolean',
        'accept_takeout' => 'boolean',
        'config_json' => 'array',
    ];

    /**
     * Ubigeo data (will be fetched from API).
     * You can implement this as an accessor or use a service to fetch from your API.
     *
     * Example structure from API:
     * [
     *   'department' => 'Lima',
     *   'province' => 'Lima',
     *   'district' => 'Miraflores',
     *   'code' => '150122'
     * ]
     */
    protected $appends = ['ubigeo'];

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
     * Get the company that owns the branch.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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
        return $this->hasMany(Warehouse::class);
    }

    /**
     * Get all POS terminals for this branch.
     */
    public function posTerminals(): HasMany
    {
        return $this->hasMany(PosTerminal::class);
    }

    /**
     * Get all tables for this branch.
     */
    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }

    /**
     * Get all table areas for this branch.
     */
    public function tableAreas(): HasMany
    {
        return $this->hasMany(TableArea::class);
    }

    /**
     * Get all kitchen stations for this branch.
     */
    public function kitchenStations(): HasMany
    {
        return $this->hasMany(KitchenStation::class);
    }

    /**
     * Get all orders for this branch.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get all expenses for this branch.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Scope a query to only include active branches.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
}
