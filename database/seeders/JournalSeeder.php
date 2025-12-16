<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Journal;
use App\Models\Sequence;
use Illuminate\Database\Seeder;

class JournalSeeder extends Seeder
{
    public function run(): void
    {
        $mainCompany = Company::whereNull('parent_id')->first() ?? Company::first();

        if (! $mainCompany) {
            $this->command->warn('No company found. Skipping JournalSeeder.');

            return;
        }

        $journals = [
            ['name' => 'NOTA DE VENTA',           'type' => 'sale',           'code' => 'NV',   'document_type_code' => null, 'is_fiscal' => false],
            ['name' => 'FACTURA DE VENTA',        'type' => 'sale',           'code' => 'F004', 'document_type_code' => '01', 'is_fiscal' => true],
            ['name' => 'BOLETA DE VENTA',         'type' => 'sale',           'code' => 'B004', 'document_type_code' => '03', 'is_fiscal' => true],
            ['name' => 'Cotizaciones',            'type' => 'quote',          'code' => 'COT',  'document_type_code' => null, 'is_fiscal' => false],
            ['name' => 'Nota de Crédito Factura', 'type' => 'credit_note',    'code' => 'FC04', 'document_type_code' => '07', 'is_fiscal' => true],
            ['name' => 'Nota de Crédito Boleta',  'type' => 'credit_note',    'code' => 'BC04', 'document_type_code' => '07', 'is_fiscal' => true],
            ['name' => 'Nota de Débito Factura',  'type' => 'debit_note',     'code' => 'FD04', 'document_type_code' => '08', 'is_fiscal' => true],
            ['name' => 'Nota de Débito Boleta',   'type' => 'debit_note',     'code' => 'BD04', 'document_type_code' => '08', 'is_fiscal' => true],
            ['name' => 'Órdenes de Compra',       'type' => 'purchase',       'code' => 'OC',   'document_type_code' => null, 'is_fiscal' => false],
            ['name' => 'Compras',                 'type' => 'purchase',       'code' => 'COMP', 'document_type_code' => null, 'is_fiscal' => false],
            ['name' => 'Traslados',               'type' => 'transfer',       'code' => 'TRF',  'document_type_code' => null, 'is_fiscal' => false],
            ['name' => 'Cuadre de Caja',          'type' => 'cash',           'code' => 'CAJA', 'document_type_code' => null, 'is_fiscal' => false],
        ];

        foreach ($journals as $journalData) {
            // Verificar si ya existe
            $existing = Journal::where('code', $journalData['code'])
                ->where('company_id', $mainCompany->id)
                ->first();

            if ($existing) {
                continue; // No duplicar
            }

            $sequence = Sequence::create([
                'sequence_size' => 8,
                'step' => 1,
                'next_number' => 1,
            ]);

            Journal::create([
                'code' => $journalData['code'],
                'name' => $journalData['name'],
                'type' => $journalData['type'],
                'document_type_code' => $journalData['document_type_code'],
                'is_fiscal' => $journalData['is_fiscal'] ?? false,
                'sequence_id' => $sequence->id,
                'company_id' => $mainCompany->id,
                'branch_id' => null, // Sin branch por ahora
            ]);
        }

        $this->command->info('Journals seeded!');
        $this->command->table(
            ['Code', 'Name', 'Type', 'Fiscal'],
            Journal::where('company_id', $mainCompany->id)
                ->get(['code', 'name', 'type', 'is_fiscal'])
                ->map(fn ($j) => [$j->code, $j->name, $j->type, $j->is_fiscal ? '✓' : ''])
                ->toArray()
        );
    }
}
