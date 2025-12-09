<?php

namespace Database\Seeders;

use App\Models\KitchenStation;
use Illuminate\Database\Seeder;

class KitchenStationsSeeder extends Seeder
{
    /**
     * Seed kitchen stations.
     */
    public function run(): void
    {
        $stations = [
            [
                'branch_id' => 1,
                'name' => 'Cocina Principal',
                'description' => 'Preparación de platos calientes',
                'printer_ip' => '192.168.1.100',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'branch_id' => 1,
                'name' => 'Bebidas y Postres',
                'description' => 'Preparación de bebidas y postres',
                'printer_ip' => '192.168.1.101',
                'order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($stations as $station) {
            KitchenStation::firstOrCreate(
                ['name' => $station['name'], 'branch_id' => $station['branch_id']],
                $station
            );
        }

        $this->command->info('Kitchen stations seeded!');
        $this->command->table(
            ['ID', 'Name'],
            KitchenStation::all(['id', 'name'])->toArray()
        );
    }
}
