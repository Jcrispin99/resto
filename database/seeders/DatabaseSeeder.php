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
            PosTerminalSeeder::class,        // ✅ Terminales antes de cajas
            PosTablesSeeder::class,
            PaymentMethodsSeeder::class,
            KitchenStationsSeeder::class,
            CashRegisterSeeder::class,       // ✅ Después de terminales y usuarios

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
