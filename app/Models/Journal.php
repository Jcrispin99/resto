<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'code',
        'type',
        'is_fiscal',
        'document_type_code',
        'sequence_id',
        'is_active',
    ];

    protected $casts = [
        'is_fiscal' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(Sequence::class);
    }

    /**
     * Genera el siguiente correlativo completo (Serie-Numero).
     * Ej: F001-00001234
     */
    public function generateNextCorrelation(bool $increment = true): string
    {
        $number = $this->sequence->getNextNumber($increment);
        return "{$this->code}-{$number}";
    }

    // Tipos de Journals
    const TYPE_SALE = 'sale';           // Ventas (Facturas, Boletas)
    const TYPE_PURCHASE = 'purchase';   // Compras
    const TYPE_QUOTE = 'quote';         // Cotizaciones
    const TYPE_CREDIT_NOTE = 'credit_note';
    const TYPE_DEBIT_NOTE = 'debit_note';
    const TYPE_DISPATCH = 'dispatch';   // Guías de Remisión
    const TYPE_CASH = 'cash';           // Recibos de caja
}
