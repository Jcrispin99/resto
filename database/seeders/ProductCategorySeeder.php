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
            'description' => 'Bebidas y refrescos',
            'code' => 'BEB',
            'is_active' => true,
        ]);

        // Subcategorías de Bebidas
        \App\Models\ProductCategory::create([
            'parent_id' => $bebidas->id,
            'name' => 'Gaseosas',
            'description' => 'Bebidas gaseosas',
            'code' => 'BEB-GAS',
            'is_active' => true,
        ]);

        \App\Models\ProductCategory::create([
            'parent_id' => $bebidas->id,
            'name' => 'Cervezas',
            'description' => 'Cervezas nacionales e importadas',
            'code' => 'BEB-CER',
            'is_active' => true,
        ]);

        // 2. Carnes y Aves
        $carnes = \App\Models\ProductCategory::create([
            'name' => 'Carnes y Aves',
            'description' => 'Carnes rojas, blancas y aves',
            'code' => 'CAR',
            'is_active' => true,
        ]);

        \App\Models\ProductCategory::create([
            'parent_id' => $carnes->id,
            'name' => 'Res',
            'description' => 'Carne de res',
            'code' => 'CAR-RES',
            'is_active' => true,
        ]);

        \App\Models\ProductCategory::create([
            'parent_id' => $carnes->id,
            'name' => 'Pollo',
            'description' => 'Carne de pollo',
            'code' => 'CAR-POL',
            'is_active' => true,
        ]);

        // 3. Verduras y Frutas
        \App\Models\ProductCategory::create([
            'name' => 'Verduras y Frutas',
            'description' => 'Productos frescos vegetales',
            'code' => 'VER',
            'is_active' => true,
        ]);

        // 4. Lácteos
        $lacteos = \App\Models\ProductCategory::create([
            'name' => 'Lácteos',
            'description' => 'Leche, quesos, yogurt, etc.',
            'code' => 'LAC',
            'is_active' => true,
        ]);

        \App\Models\ProductCategory::create([
            'parent_id' => $lacteos->id,
            'name' => 'Quesos',
            'description' => 'Quesos frescos y madurados',
            'code' => 'LAC-QUE',
            'is_active' => true,
        ]);

        // 5. Abarrotes
        $abarrotes = \App\Models\ProductCategory::create([
            'name' => 'Abarrotes',
            'description' => 'Productos secos y envasados',
            'code' => 'ABA',
            'is_active' => true,
        ]);

        \App\Models\ProductCategory::create([
            'parent_id' => $abarrotes->id,
            'name' => 'Pastas',
            'description' => 'Pastas secas y frescas',
            'code' => 'ABA-PAS',
            'is_active' => true,
        ]);

        \App\Models\ProductCategory::create([
            'parent_id' => $abarrotes->id,
            'name' => 'Condimentos',
            'description' => 'Especias y condimentos',
            'code' => 'ABA-CON',
            'is_active' => true,
        ]);

        $this->command->info('✅ Created 5 main product categories with subcategories');
    }
}
