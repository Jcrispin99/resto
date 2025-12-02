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
            ['name' => 'Unidad', 'abbreviation' => 'NIU'],
            ['name' => 'Kilogramo', 'abbreviation' => 'KGM'],
            ['name' => 'Litro', 'abbreviation' => 'LTR'],
            ['name' => 'Metro', 'abbreviation' => 'MTR'],
            ['name' => 'Servicio', 'abbreviation' => 'ZZ'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['abbreviation' => $unit['abbreviation']], $unit);
        }
    }
}
