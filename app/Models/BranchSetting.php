<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
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
        'tax_percentage' => 'decimal:2',
        'print_kitchen_ticket' => 'boolean',
        'print_customer_receipt' => 'boolean',
        'accept_reservations' => 'boolean',
        'accept_delivery' => 'boolean',
        'accept_takeout' => 'boolean',
        'config_json' => 'array',
    ];

    /**
     * Get the branch that owns the settings.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get a specific config value.
     */
    public function getConfig(string $key, mixed $default = null): mixed
    {
        return data_get($this->config_json, $key, $default);
    }

    /**
     * Set a specific config value.
     */
    public function setConfig(string $key, mixed $value): void
    {
        $config = $this->config_json ?? [];
        data_set($config, $key, $value);
        $this->config_json = $config;
    }
}
