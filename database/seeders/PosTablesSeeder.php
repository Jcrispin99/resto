<?php

namespace Database\Seeders;

use App\Models\Table;
use App\Models\TableArea;
use Illuminate\Database\Seeder;

class PosTablesSeeder extends Seeder
{
    /**
     * Seed tables and areas for POS.
     */
    public function run(): void
    {
        // Create Table Areas (using branch_id = 1)
        $salon = TableArea::firstOrCreate(
            ['name' => 'Salón Principal', 'branch_id' => 1],
            ['description' => 'Área principal del restaurante', 'is_active' => true]
        );

        $terraza = TableArea::firstOrCreate(
            ['name' => 'Terraza', 'branch_id' => 1],
            ['description' => 'Área al aire libre', 'is_active' => true]
        );

        $vip = TableArea::firstOrCreate(
            ['name' => 'VIP', 'branch_id' => 1],
            ['description' => 'Área privada', 'is_active' => true]
        );

        $barra = TableArea::firstOrCreate(
            ['name' => 'Barra', 'branch_id' => 1],
            ['description' => 'Mesas en la barra', 'is_active' => true]
        );

        // Create Tables for Salón Principal (1-10)
        for ($i = 1; $i <= 10; $i++) {
            Table::firstOrCreate(
                ['number' => $i, 'branch_id' => 1, 'area_id' => $salon->id],
                [
                    'capacity' => $i <= 4 ? 4 : 6,
                    'is_active' => true,
                ]
            );
        }

        // Create Tables for Terraza (11-16)
        for ($i = 11; $i <= 16; $i++) {
            Table::firstOrCreate(
                ['number' => $i, 'branch_id' => 1, 'area_id' => $terraza->id],
                [
                    'capacity' => 4,
                    'is_active' => true,
                ]
            );
        }

        // Create Tables for VIP (17-20)
        for ($i = 17; $i <= 20; $i++) {
            Table::firstOrCreate(
                ['number' => $i, 'branch_id' => 1, 'area_id' => $vip->id],
                [
                    'capacity' => 8,
                    'is_active' => true,
                ]
            );
        }

        // Create Bar seats (B1-B5)
        for ($i = 1; $i <= 5; $i++) {
            Table::firstOrCreate(
                ['number' => 100 + $i, 'branch_id' => 1, 'area_id' => $barra->id],
                [
                    'capacity' => 2,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('POS Tables seeded successfully!');
        $this->command->table(
            ['Area', 'Tables Count'],
            [
                [$salon->name, Table::where('area_id', $salon->id)->count()],
                [$terraza->name, Table::where('area_id', $terraza->id)->count()],
                [$vip->name, Table::where('area_id', $vip->id)->count()],
                [$barra->name, Table::where('area_id', $barra->id)->count()],
            ]
        );
    }
}
