<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['code' => 'NIU', 'name' => 'Unidad', 'abbreviation' => 'NIU'],
            ['code' => 'KGM', 'name' => 'Kilogramo', 'abbreviation' => 'KGM'],
            ['code' => 'LTR', 'name' => 'Litro', 'abbreviation' => 'LTR'],
            ['code' => 'MTR', 'name' => 'Metro', 'abbreviation' => 'MTR'],
            ['code' => 'ZZ', 'name' => 'Servicio', 'abbreviation' => 'ZZ'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['abbreviation' => $unit['abbreviation']], $unit);
        }
    }
}
