<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Journal;
use App\Models\Sequence;
use Illuminate\Database\Seeder;

class JournalSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        if (!$company) {
            $this->command->warn('No company found. Skipping JournalSeeder.');
            return;
        }

        $branches = Branch::where('company_id', $company->id)->get();

        if ($branches->isEmpty()) {
            $this->command->warn('No branches found. Creating global journals only.');
        }

        // 1. Series Globales (Cotizaciones, Compras, Notas de Venta Globales)
        $this->createGlobalJournals($company);

        // 2. Series por Sucursal (Facturas, Boletas, Notas de Crédito)
        foreach ($branches as $index => $branch) {
            // Generar códigos de serie basados en el ID o índice de la sucursal
            // Branch 1 -> F001, B001
            // Branch 2 -> F002, B002
            $seriesSuffix = str_pad($index + 1, 3, '0', STR_PAD_LEFT); // 001, 002...

            $this->createBranchJournals($company, $branch, $seriesSuffix);
        }
    }

    private function createGlobalJournals(Company $company)
    {
        $journals = [
            ['name' => 'Cotizaciones',    'type' => Journal::TYPE_QUOTE,    'code' => 'COT',  'doc_type' => null, 'fiscal' => false],
            ['name' => 'Órdenes de Compra', 'type' => Journal::TYPE_PURCHASE, 'code' => 'OC',   'doc_type' => null, 'fiscal' => false],
            ['name' => 'Compras',         'type' => Journal::TYPE_PURCHASE, 'code' => 'COMP', 'doc_type' => null, 'fiscal' => false],
        ];

        foreach ($journals as $data) {
            $this->createJournal($company, null, $data);
        }
    }

    private function createBranchJournals(Company $company, Branch $branch, string $suffix)
    {
        $journals = [
            // Ventas POS
            ['name' => "Nota de Venta {$branch->name}", 'type' => Journal::TYPE_SALE, 'code' => "NV{$suffix}", 'doc_type' => null, 'fiscal' => false],
            ['name' => "Factura {$branch->name}",       'type' => Journal::TYPE_SALE, 'code' => "F{$suffix}",  'doc_type' => '01', 'fiscal' => true],
            ['name' => "Boleta {$branch->name}",        'type' => Journal::TYPE_SALE, 'code' => "B{$suffix}",  'doc_type' => '03', 'fiscal' => true],
            
            // Notas de Crédito
            ['name' => "NC Factura {$branch->name}",    'type' => Journal::TYPE_CREDIT_NOTE, 'code' => "FC{$suffix}", 'doc_type' => '07', 'fiscal' => true],
            ['name' => "NC Boleta {$branch->name}",     'type' => Journal::TYPE_CREDIT_NOTE, 'code' => "BC{$suffix}", 'doc_type' => '07', 'fiscal' => true],
            
            // Guías (opcional)
            ['name' => "Guía Remisión {$branch->name}", 'type' => Journal::TYPE_DISPATCH,    'code' => "T{$suffix}",  'doc_type' => '09', 'fiscal' => true],
        ];

        foreach ($journals as $data) {
            $this->createJournal($company, $branch, $data);
        }
    }

    private function createJournal(Company $company, ?Branch $branch, array $data)
    {
        // Crear secuencia
        $sequence = Sequence::create([
            'sequence_size' => 8,
            'step'          => 1,
            'next_number'   => 1,
        ]);

        // Crear journal
        Journal::updateOrCreate(
            [
                'company_id' => $company->id,
                'code'       => $data['code'],
            ],
            [
                'branch_id'          => $branch?->id,
                'name'               => $data['name'],
                'type'               => $data['type'],
                'document_type_code' => $data['doc_type'],
                'is_fiscal'          => $data['fiscal'],
                'sequence_id'        => $sequence->id,
                'is_active'          => true,
            ]
        );
    }
}
