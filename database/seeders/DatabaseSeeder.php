<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Base data
            BranchManagementSeeder::class,
            WarehouseSeeder::class,
            UnitSeeder::class,
            ProductCategoriesFullSeeder::class,
            TaxSeeder::class,
            PartnerSeeder::class,

            // POS Infrastructure (must be before products)
            RolesAndPermissionsSeeder::class,
            PosUsersSeeder::class,
            PosTablesSeeder::class,
            PaymentMethodsSeeder::class,
            KitchenStationsSeeder::class, // Before MenuProductsSeeder

            // Products (after stations)
            MenuProductsSeeder::class,
        ]);

        // Default admin user
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
