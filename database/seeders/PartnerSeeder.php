<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 10 Suppliers
        Partner::factory()->count(10)->supplier()->create();

        // Create 10 Customers
        Partner::factory()->count(10)->customer()->create();
    }
}
