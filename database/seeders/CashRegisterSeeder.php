<?php

namespace Database\Seeders;

use App\Models\CashRegister;
use App\Models\CashMovement;
use App\Models\PosTerminal;
use App\Models\User;
use Illuminate\Database\Seeder;

class CashRegisterSeeder extends Seeder
{
    /**
     * Seed Cash Registers for testing.
     */
    public function run(): void
    {
        // Get terminals and users
        $terminal1 = PosTerminal::where('code', 'CAJA-01')->first();
        $terminal2 = PosTerminal::where('code', 'CAJA-02')->first();
        $terminal3 = PosTerminal::where('code', 'CAJA-03')->first();
        
        $admin = User::where('email', 'admin@pos.com')->first() ?? User::first();
        $cashier = User::where('email', 'cajero@pos.com')->first() ?? User::skip(1)->first();
        $waiter = User::where('email', 'mozo@pos.com')->first() ?? User::skip(2)->first();

        if (!$terminal1 || !$admin) {
            $this->command->warn('⚠️  No terminals or users found. Run PosTerminalSeeder and PosUsersSeeder first.');
            return;
        }

        // 1. Caja cerrada de ayer (CAJA-01)
        $yesterday = CashRegister::firstOrCreate(
            [
                'terminal_id' => $terminal1->id,
                'status' => CashRegister::STATUS_CLOSED,
                'opened_at' => now()->subDay()->setHour(9)->setMinute(0),
            ],
            [
                'opening_balance' => 100.00,
                'closing_balance' => 1250.50,
                'expected_balance' => 1250.50,
                'difference' => 0.00,
                'opened_by' => $admin->id,
                'closed_by' => $admin->id,
                'closed_at' => now()->subDay()->setHour(21)->setMinute(0),
                'notes' => 'Turno normal sin novedades',
            ]
        );

        // Movimientos de la caja cerrada
        if ($yesterday->movements()->count() === 0) {
            $yesterday->movements()->create([
                'type' => CashMovement::TYPE_OPENING,
                'concept' => 'Apertura de caja',
                'amount' => 100.00,
                'payment_method' => 'cash',
                'user_id' => $admin->id,
            ]);

            $yesterday->movements()->create([
                'type' => CashMovement::TYPE_SALE,
                'concept' => 'Venta #001',
                'amount' => 150.50,
                'payment_method' => 'cash',
                'user_id' => $admin->id,
            ]);

            $yesterday->movements()->create([
                'type' => CashMovement::TYPE_SALE,
                'concept' => 'Venta #002',
                'amount' => 1000.00,
                'payment_method' => 'card',
                'user_id' => $admin->id,
            ]);
        }

        // 2. Caja abierta HOY (CAJA-01) - Turno actual
        $todayOpen = CashRegister::firstOrCreate(
            [
                'terminal_id' => $terminal1->id,
                'status' => CashRegister::STATUS_OPEN,
                'opened_at' => now()->setHour(9)->setMinute(0),
            ],
            [
                'opening_balance' => 150.00,
                'closing_balance' => null,
                'expected_balance' => null,
                'difference' => null,
                'opened_by' => $cashier?->id ?? $admin->id,
                'closed_by' => null,
                'closed_at' => null,
                'notes' => 'Turno en progreso',
            ]
        );

        if ($todayOpen->movements()->count() === 0) {
            $todayOpen->movements()->create([
                'type' => CashMovement::TYPE_OPENING,
                'concept' => 'Apertura de caja',
                'amount' => 150.00,
                'payment_method' => 'cash',
                'user_id' => $cashier?->id ?? $admin->id,
            ]);

            $todayOpen->movements()->create([
                'type' => CashMovement::TYPE_SALE,
                'concept' => 'Venta #003',
                'amount' => 250.00,
                'payment_method' => 'cash',
                'user_id' => $cashier?->id ?? $admin->id,
            ]);
        }

        // 3. Caja abierta en BARRA (CAJA-02)
        if ($terminal2) {
            $barraOpen = CashRegister::firstOrCreate(
                [
                    'terminal_id' => $terminal2->id,
                    'status' => CashRegister::STATUS_OPEN,
                    'opened_at' => now()->setHour(10)->setMinute(0),
                ],
                [
                    'opening_balance' => 50.00,
                    'closing_balance' => null,
                    'expected_balance' => null,
                    'difference' => null,
                    'opened_by' => $waiter?->id ?? $admin->id,
                    'closed_by' => null,
                    'closed_at' => null,
                    'notes' => 'Turno barra',
                ]
            );

            if ($barraOpen->movements()->count() === 0) {
                $barraOpen->movements()->create([
                    'type' => CashMovement::TYPE_OPENING,
                    'concept' => 'Apertura de caja',
                    'amount' => 50.00,
                    'payment_method' => 'cash',
                    'user_id' => $waiter?->id ?? $admin->id,
                ]);
            }
        }

        // 4. Caja cerrada con diferencia (CAJA-03) - Anteayer
        if ($terminal3) {
            $withDifference = CashRegister::firstOrCreate(
                [
                    'terminal_id' => $terminal3->id,
                    'status' => CashRegister::STATUS_CLOSED,
                    'opened_at' => now()->subDays(2)->setHour(9)->setMinute(0),
                ],
                [
                    'opening_balance' => 100.00,
                    'closing_balance' => 890.00,
                    'expected_balance' => 895.00,
                    'difference' => -5.00, // Faltaron S/. 5
                    'opened_by' => $admin->id,
                    'closed_by' => $admin->id,
                    'closed_at' => now()->subDays(2)->setHour(21)->setMinute(0),
                    'notes' => 'Diferencia de S/. 5.00 - revisar',
                ]
            );

            if ($withDifference->movements()->count() === 0) {
                $withDifference->movements()->create([
                    'type' => CashMovement::TYPE_OPENING,
                    'concept' => 'Apertura de caja',
                    'amount' => 100.00,
                    'payment_method' => 'cash',
                    'user_id' => $admin->id,
                ]);

                $withDifference->movements()->create([
                    'type' => CashMovement::TYPE_SALE,
                    'concept' => 'Ventas del día',
                    'amount' => 795.00,
                    'payment_method' => 'cash',
                    'user_id' => $admin->id,
                ]);
            }
        }

        $this->command->info('✅ Cash Registers seeded successfully!');
        $this->command->table(
            ['Terminal', 'Status', 'Opened By', 'Opening', 'Closing', 'Difference'],
            CashRegister::with(['terminal', 'openedBy'])->latest('opened_at')->get()->map(fn($cr) => [
                $cr->terminal->code ?? 'N/A',
                $cr->status === 'open' ? '🟢 OPEN' : '🔴 CLOSED',
                $cr->openedBy->name ?? 'N/A',
                'S/. ' . number_format($cr->opening_balance, 2),
                $cr->closing_balance ? 'S/. ' . number_format($cr->closing_balance, 2) : '-',
                $cr->difference ? 'S/. ' . number_format($cr->difference, 2) : '-',
            ])->toArray()
        );
    }
}
