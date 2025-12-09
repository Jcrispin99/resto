<?php

namespace Database\Seeders;

use App\Models\KitchenStation;
use App\Models\ProductCategory;
use App\Models\ProductMenuSettings;
use App\Models\ProductProduct;
use App\Models\ProductTemplate;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class MenuProductsSeeder extends Seeder
{
    /**
     * Seed menu products for POS with kitchen stations.
     */
    public function run(): void
    {
        // Get default unit
        $unit = Unit::firstOrCreate(
            ['name' => 'Unidad'],
            ['abbreviation' => 'und', 'is_active' => true]
        );

        // Get kitchen stations
        $cocinaPrincipal = KitchenStation::where('name', 'Cocina Principal')->first();
        $bebidasPostres = KitchenStation::where('name', 'Bebidas y Postres')->first();

        // Get menu categories
        $entradas = ProductCategory::where('name', 'Entradas')->where('type', 'menu')->first();
        $platosFuertes = ProductCategory::where('name', 'Platos Fuertes')->where('type', 'menu')->first();
        $bebidas = ProductCategory::where('name', 'Bebidas')->where('type', 'menu')->first();
        $postres = ProductCategory::where('name', 'Postres')->where('type', 'menu')->first();
        $promociones = ProductCategory::where('name', 'Promociones')->where('type', 'menu')->first();

        if (!$entradas || !$platosFuertes || !$bebidas || !$postres) {
            $this->command->error('Categories not found! Run ProductCategoriesFullSeeder first.');
            return;
        }

        // Products: [name, category, station, price, description, prep_time]
        $products = [
            // Entradas → Cocina Principal
            ['Ceviche Clásico', $entradas, $cocinaPrincipal, 35.00, 'Pescado fresco marinado en limón', 15],
            ['Tequeños', $entradas, $cocinaPrincipal, 16.00, 'Tequeños con huancaína', 8],
            ['Papa a la Huancaína', $entradas, $cocinaPrincipal, 14.00, 'Papas con salsa huancaína', 10],
            
            // Platos Fuertes → Cocina Principal
            ['Lomo Saltado', $platosFuertes, $cocinaPrincipal, 38.00, 'Lomo con papas fritas y arroz', 20],
            ['Pollo a la Brasa', $platosFuertes, $cocinaPrincipal, 42.00, 'Pollo entero con papas', 35],
            ['Arroz con Mariscos', $platosFuertes, $cocinaPrincipal, 45.00, 'Arroz con mariscos', 25],
            ['Ají de Gallina', $platosFuertes, $cocinaPrincipal, 28.00, 'Ají de gallina con arroz', 20],
            ['Arroz Chaufa', $platosFuertes, $cocinaPrincipal, 26.00, 'Arroz saltado estilo oriental', 15],
            
            // Bebidas → Bebidas y Postres
            ['Chicha Morada', $bebidas, $bebidasPostres, 8.00, 'Bebida de maíz morado', 0],
            ['Limonada', $bebidas, $bebidasPostres, 7.00, 'Limonada natural', 3],
            ['Inca Kola', $bebidas, $bebidasPostres, 5.00, 'Gaseosa 500ml', 0],
            ['Pisco Sour', $bebidas, $bebidasPostres, 22.00, 'Cóctel peruano', 5],
            ['Cerveza Cusqueña', $bebidas, $bebidasPostres, 12.00, 'Cerveza 620ml', 0],
            
            // Postres → Bebidas y Postres
            ['Suspiro Limeño', $postres, $bebidasPostres, 14.00, 'Dulce de leche con merengue', 5],
            ['Helado 3 Sabores', $postres, $bebidasPostres, 12.00, 'Vainilla, chocolate y fresa', 2],
            ['Arroz con Leche', $postres, $bebidasPostres, 10.00, 'Postre tradicional', 5],
            
            // Promociones → Cocina Principal
            ['Menú del Día', $promociones, $cocinaPrincipal, 18.00, 'Entrada + Segundo + Bebida', 25],
        ];

        $count = 0;
        foreach ($products as $product) {
            [$name, $category, $station, $price, $description, $prepTime] = $product;

            // Create ProductTemplate with station
            $template = ProductTemplate::firstOrCreate(
                ['name' => $name],
                [
                    'category_id' => $category->id,
                    'menu_category_id' => $category->id,
                    'unit_id' => $unit->id,
                    'kitchen_station_id' => $station?->id,
                    'description' => $description,
                    'product_type' => 'service',
                    'can_be_sold' => true,
                    'can_be_purchased' => false,
                    'can_be_stocked' => false,
                    'sale_price' => $price,
                    'is_active' => true,
                ]
            );

            // Create default variant
            $sku = 'MENU-' . str_pad($template->id, 4, '0', STR_PAD_LEFT);
            ProductProduct::firstOrCreate(
                ['template_id' => $template->id],
                ['sku' => $sku, 'is_active' => true]
            );

            // Create menu settings
            ProductMenuSettings::firstOrCreate(
                ['product_template_id' => $template->id],
                [
                    'menu_description' => $description,
                    'preparation_time_minutes' => $prepTime,
                    'is_featured' => $price >= 35,
                    'display_order' => $count,
                    'available_for_dine_in' => true,
                    'available_for_takeout' => true,
                    'available_for_delivery' => true,
                ]
            );

            $count++;
        }

        $this->command->info("Menu products seeded: $count products");
        $this->command->table(
            ['Category', 'Products'],
            [
                ['Entradas', ProductTemplate::where('menu_category_id', $entradas->id)->count()],
                ['Platos Fuertes', ProductTemplate::where('menu_category_id', $platosFuertes->id)->count()],
                ['Bebidas', ProductTemplate::where('menu_category_id', $bebidas->id)->count()],
                ['Postres', ProductTemplate::where('menu_category_id', $postres->id)->count()],
                ['Promociones', ProductTemplate::where('menu_category_id', $promociones->id)->count()],
            ]
        );
    }
}
