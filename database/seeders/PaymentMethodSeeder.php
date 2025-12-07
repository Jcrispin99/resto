<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'code' => 'cash',
                'name' => 'Efectivo',
                'type' => 'cash',
                'requires_reference' => false,
                'is_active' => true,
            ],
            [
                'code' => 'visa',
                'name' => 'Tarjeta Visa',
                'type' => 'card',
                'requires_reference' => true,
                'is_active' => true,
            ],
            [
                'code' => 'mastercard',
                'name' => 'Tarjeta Mastercard',
                'type' => 'card',
                'requires_reference' => true,
                'is_active' => true,
            ],
            [
                'code' => 'amex',
                'name' => 'American Express',
                'type' => 'card',
                'requires_reference' => true,
                'is_active' => true,
            ],
            [
                'code' => 'yape',
                'name' => 'Yape',
                'type' => 'wallet',
                'requires_reference' => true,
                'is_active' => true,
            ],
            [
                'code' => 'plin',
                'name' => 'Plin',
                'type' => 'wallet',
                'requires_reference' => true,
                'is_active' => true,
            ],
            [
                'code' => 'transfer',
                'name' => 'Transferencia Bancaria',
                'type' => 'transfer',
                'requires_reference' => true,
                'is_active' => true,
            ],
            [
                'code' => 'deposit',
                'name' => 'Depósito Bancario',
                'type' => 'transfer',
                'requires_reference' => true,
                'is_active' => true,
            ],
            [
                'code' => 'credit',
                'name' => 'Crédito',
                'type' => 'other',
                'requires_reference' => false,
                'is_active' => true,
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}
