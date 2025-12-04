<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BranchManagementSeeder::class,
            WarehouseSeeder::class,
            UnitSeeder::class,
            ProductCategorySeeder::class,
            TaxSeeder::class,
            PartnerSeeder::class,
            ProductSeeder::class,
        ]);

        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );
    }
}
