<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Bebidas
        $bebidas = \App\Models\ProductCategory::create([
            'name' => 'Bebidas',
            'is_active' => true,
        ]);

        // Subcategorías de Bebidas
        \App\Models\ProductCategory::create([
            'parent_id' => $bebidas->id,
            'name' => 'Gaseosas',
            'is_active' => true,
        ]);

        \App\Models\ProductCategory::create([
            'parent_id' => $bebidas->id,
            'name' => 'Cervezas',
            'is_active' => true,
        ]);

        // 2. Carnes y Aves
        $carnes = \App\Models\ProductCategory::create([
            'name' => 'Carnes y Aves',
            'is_active' => true,
        ]);

        \App\Models\ProductCategory::create([
            'parent_id' => $carnes->id,
            'name' => 'Res',
            'is_active' => true,
        ]);

        \App\Models\ProductCategory::create([
            'parent_id' => $carnes->id,
            'name' => 'Pollo',
            'is_active' => true,
        ]);

        // 3. Verduras y Frutas
        \App\Models\ProductCategory::create([
            'name' => 'Verduras y Frutas',
            'is_active' => true,
        ]);

        // 4. Lácteos
        $lacteos = \App\Models\ProductCategory::create([
            'name' => 'Lácteos',
            'is_active' => true,
        ]);

        \App\Models\ProductCategory::create([
            'parent_id' => $lacteos->id,
            'name' => 'Quesos',
            'is_active' => true,
        ]);

        // 5. Abarrotes
        $abarrotes = \App\Models\ProductCategory::create([
            'name' => 'Abarrotes',
            'is_active' => true,
        ]);

        \App\Models\ProductCategory::create([
            'parent_id' => $abarrotes->id,
            'name' => 'Pastas',
            'is_active' => true,
        ]);

        \App\Models\ProductCategory::create([
            'parent_id' => $abarrotes->id,
            'name' => 'Condimentos',
            'is_active' => true,
        ]);

        $this->command->info('✅ Created 5 main product categories with subcategories');
    }
}
