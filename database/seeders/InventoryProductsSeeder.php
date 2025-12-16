<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\ProductProduct;
use App\Models\ProductTemplate;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class InventoryProductsSeeder extends Seeder
{
    /**
     * Seed inventory products (raw materials, supplies, purchasable items).
     */
    public function run(): void
    {
        // Get or create units
        $unidad = Unit::firstOrCreate(['name' => 'Unidad'], ['abbreviation' => 'und', 'is_active' => true]);
        $kg = Unit::firstOrCreate(['name' => 'Kilogramo'], ['abbreviation' => 'kg', 'is_active' => true]);
        $lt = Unit::firstOrCreate(['name' => 'Litro'], ['abbreviation' => 'lt', 'is_active' => true]);
        $bolsa = Unit::firstOrCreate(['name' => 'Bolsa'], ['abbreviation' => 'bls', 'is_active' => true]);

        // Get inventory categories
        $materiasPrimas = ProductCategory::where('name', 'Materias Primas')->where('type', 'inventory')->first();
        $insumos = ProductCategory::where('name', 'Insumos')->where('type', 'inventory')->first();
        $bebidas = ProductCategory::where('name', 'Bebidas Inventario')->where('type', 'inventory')->first();
        $empaques = ProductCategory::where('name', 'Empaques')->where('type', 'inventory')->first();

        // Create missing categories if needed
        if (!$materiasPrimas) {
            $materiasPrimas = ProductCategory::create([
                'name' => 'Materias Primas',
                'type' => 'inventory',
                'is_active' => true,
            ]);
        }
        if (!$insumos) {
            $insumos = ProductCategory::create([
                'name' => 'Insumos',
                'type' => 'inventory',
                'is_active' => true,
            ]);
        }
        if (!$bebidas) {
            $bebidas = ProductCategory::create([
                'name' => 'Bebidas Inventario',
                'type' => 'inventory',
                'is_active' => true,
            ]);
        }
        if (!$empaques) {
            $empaques = ProductCategory::create([
                'name' => 'Empaques',
                'type' => 'inventory',
                'is_active' => true,
            ]);
        }

        // Products: [name, category, unit, type, can_be_sold, sale_price, description]
        $products = [
            // Materias Primas (solo compra, no venta)
            ['Arroz Granel', $materiasPrimas, $kg, 'storable', false, 0, 'Arroz para cocina'],
            ['Aceite Vegetal 5L', $materiasPrimas, $unidad, 'storable', false, 0, 'Aceite para freír'],
            ['Pollo Entero', $materiasPrimas, $kg, 'storable', false, 0, 'Pollo fresco para preparar'],
            ['Pescado Fresco', $materiasPrimas, $kg, 'storable', false, 0, 'Pescado para ceviche'],
            ['Papa Blanca', $materiasPrimas, $kg, 'storable', false, 0, 'Papas frescas'],
            ['Cebolla Roja', $materiasPrimas, $kg, 'storable', false, 0, 'Cebolla para cocina'],
            ['Limón', $materiasPrimas, $kg, 'storable', false, 0, 'Limones frescos'],
            ['Ají Amarillo', $materiasPrimas, $kg, 'storable', false, 0, 'Ají para salsas'],
            
            // Insumos
            ['Sal de Mesa 1kg', $insumos, $unidad, 'storable', false, 0, 'Sal para cocina'],
            ['Azúcar Rubia 1kg', $insumos, $unidad, 'storable', false, 0, 'Azúcar para postres'],
            ['Pimienta Molida', $insumos, $unidad, 'storable', false, 0, 'Pimienta para sazonar'],
            ['Ajo Molido', $insumos, $unidad, 'storable', false, 0, 'Ajo para cocina'],
            
            // Bebidas (compra Y venta) - aparecen en ambos módulos
            ['Coca-Cola 500ml', $bebidas, $unidad, 'storable', true, 5.00, 'Gaseosa personal'],
            ['Coca-Cola 1.5L', $bebidas, $unidad, 'storable', true, 9.00, 'Gaseosa familiar'],
            ['Inca Kola 500ml', $bebidas, $unidad, 'storable', true, 5.00, 'Gaseosa personal'],
            ['Agua San Luis 500ml', $bebidas, $unidad, 'storable', true, 3.00, 'Agua mineral'],
            ['Cerveza Pilsen 620ml', $bebidas, $unidad, 'storable', true, 10.00, 'Cerveza grande'],
            ['Cerveza Cusqueña 620ml', $bebidas, $unidad, 'storable', true, 12.00, 'Cerveza premium'],
            
            // Empaques
            ['Bolsa Delivery Grande', $empaques, $bolsa, 'consumable', false, 0, 'Bolsa para delivery'],
            ['Bolsa Delivery Pequeña', $empaques, $bolsa, 'consumable', false, 0, 'Bolsa para delivery'],
            ['Contenedor Tecnopor', $empaques, $unidad, 'consumable', false, 0, 'Para llevar comida'],
            ['Cubiertos Descartables', $empaques, $unidad, 'consumable', false, 0, 'Set de cubiertos'],
        ];

        $count = 0;
        foreach ($products as $product) {
            [$name, $category, $unit, $productType, $canBeSold, $salePrice, $description] = $product;

            // Create ProductTemplate
            $template = ProductTemplate::firstOrCreate(
                ['name' => $name],
                [
                    'category_id' => $category->id,
                    'menu_category_id' => $canBeSold ? $this->getMenuBeverageCategory()?->id : null,
                    'unit_id' => $unit->id,
                    'description' => $description,
                    'product_type' => $productType,
                    'can_be_sold' => $canBeSold,
                    'can_be_purchased' => true, // Always true for inventory
                    'can_be_stocked' => $productType === 'storable',
                    'sale_price' => $salePrice,
                    'is_active' => true,
                ]
            );

            // Create default variant
            $sku = 'INV-' . str_pad($template->id, 4, '0', STR_PAD_LEFT);
            ProductProduct::firstOrCreate(
                ['template_id' => $template->id],
                ['sku' => $sku, 'is_active' => true]
            );

            $count++;
        }

        $this->command->info("Inventory products seeded: $count products");
        $this->command->table(
            ['Category', 'Products'],
            [
                ['Materias Primas', ProductTemplate::where('category_id', $materiasPrimas->id)->count()],
                ['Insumos', ProductTemplate::where('category_id', $insumos->id)->count()],
                ['Bebidas Inventario', ProductTemplate::where('category_id', $bebidas->id)->count()],
                ['Empaques', ProductTemplate::where('category_id', $empaques->id)->count()],
            ]
        );
    }

    private function getMenuBeverageCategory(): ?ProductCategory
    {
        return ProductCategory::where('name', 'Bebidas')->where('type', 'menu')->first();
    }
}
