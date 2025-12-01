<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'movement_type',
        'reference_type',
        'reference_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'note',
        'user_id',
        'movement_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'movement_date' => 'datetime',
    ];

    /**
     * Get the warehouse.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the product variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductProduct::class, 'product_id');
    }

    /**
     * Get the user who created the movement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for specific movement type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('movement_type', $type);
    }

    /**
     * Scope for date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('movement_date', [$startDate, $endDate]);
    }

    // Movement types
    const TYPE_IN = 'in';               // Entrada
    const TYPE_OUT = 'out';             // Salida
    const TYPE_ADJUSTMENT = 'adjustment'; // Ajuste
    const TYPE_TRANSFER = 'transfer';    // Transferencia
    const TYPE_PURCHASE = 'purchase';    // Compra
    const TYPE_SALE = 'sale';           // Venta
    const TYPE_PRODUCTION = 'production'; // Producción
    const TYPE_RETURN = 'return';       // Devolución
}
