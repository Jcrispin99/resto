<?php

namespace Database\Seeders;

use App\Models\PosTerminal;
use Illuminate\Database\Seeder;

class PosTerminalSeeder extends Seeder
{
    /**
     * Seed POS Terminals for testing.
     */
    public function run(): void
    {
        $terminals = [
            [
                'branch_id' => 1,
                'code' => 'CAJA-01',
                'name' => 'Mostrador Principal',
                'ip_address' => '192.168.1.10',
                'printer_ip' => '192.168.1.110',
                'is_active' => true,
            ],
            [
                'branch_id' => 1,
                'code' => 'CAJA-02',
                'name' => 'Barra',
                'ip_address' => '192.168.1.11',
                'printer_ip' => '192.168.1.111',
                'is_active' => true,
            ],
            [
                'branch_id' => 1,
                'code' => 'CAJA-03',
                'name' => 'Segundo Piso',
                'ip_address' => '192.168.1.12',
                'printer_ip' => '192.168.1.112',
                'is_active' => true,
            ],
            [
                'branch_id' => 1,
                'code' => 'CAJA-04',
                'name' => 'Delivery',
                'ip_address' => '192.168.1.13',
                'printer_ip' => null, // Sin impresora fiscal
                'is_active' => true,
            ],
        ];

        foreach ($terminals as $terminal) {
            PosTerminal::firstOrCreate(
                ['code' => $terminal['code'], 'branch_id' => $terminal['branch_id']],
                $terminal
            );
        }

        $this->command->info('✅ POS Terminals seeded successfully!');
        $this->command->table(
            ['Code', 'Name', 'IP', 'Printer IP', 'Active'],
            PosTerminal::all()->map(fn ($t) => [
                $t->code,
                $t->name,
                $t->ip_address,
                $t->printer_ip ?? 'N/A',
                $t->is_active ? 'Yes' : 'No',
            ])->toArray()
        );
    }
}
