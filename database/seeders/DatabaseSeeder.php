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
            BranchManagementSeeder::class,
            JournalSeeder::class,
            WarehouseSeeder::class,
            UnitSeeder::class,
            ProductCategoriesFullSeeder::class,
            TaxSeeder::class,
            PartnerSeeder::class,

            RolesAndPermissionsSeeder::class,
            PosUsersSeeder::class,
            PosTablesSeeder::class,
            PaymentMethodsSeeder::class,
            KitchenStationsSeeder::class, // Before MenuProductsSeeder

            // Products (after stations)
            InventoryProductsSeeder::class,
            MenuProductsSeeder::class,
            RecipeSeeder::class,
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
