<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategoriesFullSeeder extends Seeder
{
    /**
     * Seed product categories with inventory and menu types.
     */
    public function run(): void
    {
        // ========================
        // INVENTORY Categories (Consumibles/Almacenables) - Reduced for testing
        // ========================
        $inventoryCategories = [
            'Insumos' => ['Carnes', 'Verduras', 'Lácteos'],
            'Bebidas Base' => ['Licores', 'Refrescos'],
            'Descartables' => ['Envases', 'Servilletas'],
        ];

        foreach ($inventoryCategories as $parentName => $children) {
            $parent = ProductCategory::firstOrCreate(
                ['name' => $parentName, 'type' => 'inventory'],
                ['is_active' => true, 'full_name' => $parentName]
            );

            foreach ($children as $childName) {
                ProductCategory::firstOrCreate(
                    ['name' => $childName, 'type' => 'inventory', 'parent_id' => $parent->id],
                    ['is_active' => true, 'full_name' => "$parentName / $childName"]
                );
            }
        }

        // ========================
        // MENU Categories (5 principales para POS)
        // ========================
        $menuCategories = [
            'Entradas' => [],
            'Platos Fuertes' => [],
            'Bebidas' => [],
            'Postres' => [],
            'Promociones' => [],
        ];

        foreach ($menuCategories as $name => $children) {
            ProductCategory::firstOrCreate(
                ['name' => $name, 'type' => 'menu'],
                ['is_active' => true, 'full_name' => $name]
            );
        }

        // Summary
        $inventoryCount = ProductCategory::where('type', 'inventory')->count();
        $menuCount = ProductCategory::where('type', 'menu')->count();

        $this->command->info('Product Categories seeded!');
        $this->command->table(
            ['Type', 'Count'],
            [
                ['inventory', $inventoryCount],
                ['menu', $menuCount],
            ]
        );
    }
}
