<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'sequence_size',
        'step',
        'next_number',
    ];

    /**
     * Obtiene el siguiente número y avanza la secuencia.
     * Retorna el número formateado (ej: "00000123")
     */
    public function getNextNumber(bool $increment = true): string
    {
        $number = $this->next_number;
        
        if ($increment) {
            $this->next_number += $this->step;
            $this->save();
        }

        return str_pad((string)$number, $this->sequence_size, '0', STR_PAD_LEFT);
    }
}
