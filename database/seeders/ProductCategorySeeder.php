<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // ========================================
        // CATEGORÍAS DE MENÚ/POS
        // ========================================
        
        $menuCategories = [
            ['name' => 'Bebidas', 'type' => 'menu'],
            ['name' => 'Entradas', 'type' => 'menu'],
            ['name' => 'Platos Principales', 'type' => 'menu'],
            ['name' => 'Postres', 'type' => 'menu'],
            ['name' => 'Guarniciones', 'type' => 'menu'],
            ['name' => 'Sandwiches', 'type' => 'menu'],
            ['name' => 'Ensaladas', 'type' => 'menu'],
        ];

        foreach ($menuCategories as $category) {
            ProductCategory::create([
                'name' => $category['name'],
                'type' => $category['type'],
                'is_active' => true,
            ]);
        }

        // ========================================
        // CATEGORÍAS DE INVENTARIO
        // ========================================
        
        $inventoryCategories = [
            ['name' => 'Materias Primas', 'type' => 'inventory'],
            ['name' => 'Insumos', 'type' => 'inventory'],
            ['name' => 'Suministros', 'type' => 'inventory'],
            ['name' => 'Productos de Limpieza', 'type' => 'inventory'],
            ['name' => 'Empaques y Embalajes', 'type' => 'inventory'],
        ];

        foreach ($inventoryCategories as $category) {
            ProductCategory::create([
                'name' => $category['name'],
                'type' => $category['type'],
                'is_active' => true,
            ]);
        }
    }
}
