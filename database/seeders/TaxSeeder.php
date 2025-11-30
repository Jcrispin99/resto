<?php

namespace Database\Seeders;

use App\Models\Tax;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxes = [
            // IGV - Impuesto General a las Ventas (18%)
            [
                'name' => 'IGV 18%',
                'description' => 'Impuesto General a las Ventas del 18%',
                'invoice_label' => 'IGV',
                'tax_type' => Tax::TYPE_IGV,
                'affectation_type_code' => Tax::AFFECTATION_GRAVADO, // 10 - Gravado
                'rate_percent' => 18.00,
                'is_price_inclusive' => false,
                'is_active' => true,
                'is_default' => true,
            ],

            // ICBPER - Impuesto a las Bolsas Plásticas
            [
                'name' => 'ICBPER',
                'description' => 'Impuesto al Consumo de Bolsas Plásticas',
                'invoice_label' => 'Bolsa Plástica',
                'tax_type' => Tax::TYPE_ICBPER,
                'affectation_type_code' => null,
                'rate_percent' => 0.40, // S/ 0.40 por bolsa (actualizado 2024)
                'is_price_inclusive' => false,
                'is_active' => true,
                'is_default' => false,
            ],

            // ISC Bebidas - Impuesto Selectivo al Consumo
            [
                'name' => 'ISC Bebidas 25%',
                'description' => 'Impuesto Selectivo al Consumo para bebidas alcohólicas',
                'invoice_label' => 'ISC',
                'tax_type' => Tax::TYPE_ISC,
                'affectation_type_code' => Tax::AFFECTATION_GRAVADO,
                'rate_percent' => 25.00,
                'is_price_inclusive' => false,
                'is_active' => true,
                'is_default' => false,
            ],

            // Exonerado
            [
                'name' => 'Exonerado',
                'description' => 'Operación exonerada de IGV',
                'invoice_label' => 'EXONERADO',
                'tax_type' => 'EXONERADO',
                'affectation_type_code' => Tax::AFFECTATION_EXONERADO, // 20
                'rate_percent' => 0.00,
                'is_price_inclusive' => false,
                'is_active' => true,
                'is_default' => false,
            ],

            // Inafecto
            [
                'name' => 'Inafecto',
                'description' => 'Operación inafecta de IGV',
                'invoice_label' => 'INAFECTO',
                'tax_type' => 'INAFECTO',
                'affectation_type_code' => Tax::AFFECTATION_INAFECTO, // 30
                'rate_percent' => 0.00,
                'is_price_inclusive' => false,
                'is_active' => true,
                'is_default' => false,
            ],

            // Gratuito
            [
                'name' => 'Gratuito',
                'description' => 'Transferencia gratuita',
                'invoice_label' => 'GRATUITO',
                'tax_type' => 'GRATUITO',
                'affectation_type_code' => Tax::AFFECTATION_GRATUITO, // 11
                'rate_percent' => 0.00,
                'is_price_inclusive' => false,
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        foreach ($taxes as $taxData) {
            Tax::create($taxData);
        }

        $this->command->info('✅ Created ' . count($taxes) . ' SUNAT taxes');
    }
}
