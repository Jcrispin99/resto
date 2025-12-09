<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodsSeeder extends Seeder
{
    /**
     * Seed payment methods for POS.
     */
    public function run(): void
    {
        $methods = [
            [
                'code' => 'CASH',
                'name' => 'Efectivo',
                'type' => 'cash',
                'requires_reference' => false,
                'is_active' => true,
            ],
            [
                'code' => 'BANK',
                'name' => 'Transferencia Bancaria',
                'type' => 'transfer',
                'requires_reference' => true,
                'is_active' => true,
            ],
            [
                'code' => 'YAPE',
                'name' => 'Yape',
                'type' => 'wallet',
                'requires_reference' => true,
                'is_active' => true,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::firstOrCreate(
                ['code' => $method['code']],
                $method
            );
        }

        $this->command->info('Payment methods seeded successfully!');
        $this->command->table(
            ['Code', 'Name', 'Type'],
            PaymentMethod::all(['code', 'name', 'type'])->toArray()
        );
    }
}
