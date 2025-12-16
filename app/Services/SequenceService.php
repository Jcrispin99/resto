<?php

namespace App\Services;

use App\Models\Journal;
use App\Models\Sequence;
use Illuminate\Support\Facades\DB;

class SequenceService
{
    /**
     * Obtiene el siguiente número de secuencia para un journal.
     * Usa lockForUpdate para evitar condiciones de carrera.
     *
     * @return array{serie: string, correlative: string, full_number: string}
     */
    public function getNextNumber(int $journalId): array
    {
        return DB::transaction(function () use ($journalId) {
            $journal = Journal::findOrFail($journalId);

            // Lock para concurrencia
            $sequence = Sequence::where('id', $journal->sequence_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Formatear correlativo con ceros
            $correlative = str_pad(
                (string) $sequence->next_number,
                $sequence->sequence_size,
                '0',
                STR_PAD_LEFT
            );

            // Incrementar secuencia
            $sequence->next_number += $sequence->step;
            $sequence->save();

            return [
                'serie' => $journal->code,
                'correlative' => $correlative,
                'full_number' => "{$journal->code}-{$correlative}",
            ];
        });
    }

    /**
     * Obtiene el siguiente número sin incrementar (preview).
     */
    public function previewNextNumber(int $journalId): string
    {
        $journal = Journal::with('sequence')->findOrFail($journalId);

        $correlative = str_pad(
            (string) $journal->sequence->next_number,
            $journal->sequence->sequence_size,
            '0',
            STR_PAD_LEFT
        );

        return "{$journal->code}-{$correlative}";
    }
}
