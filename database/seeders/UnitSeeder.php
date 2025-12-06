<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

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
