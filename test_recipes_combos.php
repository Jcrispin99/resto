#!/usr/bin/env php
<?php

/**
 * Test script for Recipes and Combos functionality
 * Run: php test_recipes_combos.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProductTemplate;
use App\Models\ProductProduct;
use App\Models\Recipe;
use App\Models\Combo;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\IngredientDeductionService;

echo "🧪 Testing Recipes and Combos System\n";
echo str_repeat("=", 50) . "\n\n";

// ============================================
// TEST 1: Create a Recipe
// ============================================
echo "📝 TEST 1: Creating a Recipe\n";
echo str_repeat("-", 50) . "\n";

try {
    // Get a product template (dish)
    $ceviche = ProductTemplate::where('name', 'LIKE', '%Ceviche%')->first();
    
    if (!$ceviche) {
        echo "⚠️  Creating test product: Ceviche\n";
        $menuCategory = \App\Models\ProductCategory::where('type', 'menu')->first();
        $unit = Unit::first();
        
        $ceviche = ProductTemplate::create([
            'name' => 'Ceviche de Pescado',
            'menu_category_id' => $menuCategory->id,
            'unit_id' => $unit->id,
            'can_be_sold' => true,
            'sale_price' => 35.00,
        ]);
        echo "   ✅ Product created: {$ceviche->name}\n";
    } else {
        echo "   📌 Using existing product: {$ceviche->name}\n";
    }
    
    // Get an ingredient
    $pescado = ProductProduct::whereHas('template', function($q) {
        $q->where('name', 'LIKE', '%Pescado%');
    })->first();
    
    if (!$pescado) {
        echo "⚠️  No ingredient found. Creating test ingredient...\n";
        $inventoryCategory = \App\Models\ProductCategory::where('type', 'inventory')->first();
        $unit = Unit::where('name', 'Kilogramo')->orWhere('abbreviation', 'kg')->first();
        
        $pescadoTemplate = ProductTemplate::create([
            'name' => 'Pescado Fresco',
            'category_id' => $inventoryCategory->id,
            'unit_id' => $unit->id,
            'can_be_purchased' => true,
            'can_be_stocked' => true,
        ]);
        
        $pescado = $pescadoTemplate->products()->create([
            'sku' => 'ING-PESCADO-001',
        ]);
        echo "   ✅ Ingredient created: {$pescadoTemplate->name}\n";
    } else {
        echo "   📌 Using existing ingredient: {$pescado->template->name}\n";
    }
    
    // Create recipe
    $unit = Unit::where('name', 'Gramo')->orWhere('abbreviation', 'g')->first() ?? Unit::first();
    
    $recipe = Recipe::create([
        'product_template_id' => $ceviche->id,
        'ingredient_id' => $pescado->id,
        'quantity' => 200, // 200 gramos
        'unit_id' => $unit->id,
        'waste_percentage' => 15, // 15% merma
        'notes' => 'Pescado fresco del día',
    ]);
    
    echo "   ✅ Recipe created successfully!\n";
    echo "      - Dish: {$ceviche->name}\n";
    echo "      - Ingredient: {$pescado->template->name}\n";
    echo "      - Quantity: {$recipe->quantity} {$unit->name}\n";
    echo "      - Waste: {$recipe->waste_percentage}%\n";
    
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// ============================================
// TEST 2: Check Inventory Availability
// ============================================
echo "📦 TEST 2: Checking Inventory Availability\n";
echo str_repeat("-", 50) . "\n";

try {
    $warehouse = Warehouse::first();
    
    if (!$warehouse) {
        echo "   ⚠️  No warehouse found. Skipping test.\n";
    } else {
        $service = new IngredientDeductionService();
        $availability = $service->checkInventoryAvailability($ceviche, 5, $warehouse);
        
        echo "   Checking for 5 portions of {$ceviche->name}\n";
        echo "   Can fulfill: " . ($availability['can_fulfill'] ? '✅ YES' : '❌ NO') . "\n";
        echo "\n   Items:\n";
        
        foreach ($availability['items'] as $item) {
            $status = $item['sufficient'] ? '✅' : '❌';
            echo "   {$status} {$item['ingredient']}: {$item['required']} {$item['unit']} needed, {$item['available']} available\n";
        }
    }
    
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// ============================================
// TEST 3: Create a Combo
// ============================================
echo "🎁 TEST 3: Creating a Combo\n";
echo str_repeat("-", 50) . "\n";

try {
    // Get products for combo
    $product1 = ProductTemplate::where('can_be_sold', true)->first();
    $product2 = ProductTemplate::where('can_be_sold', true)->skip(1)->first();
    
    if (!$product1 || !$product2) {
        echo "   ⚠️  Not enough products for combo. Skipping test.\n";
    } else {
        $combo = Combo::create([
            'name' => 'Combo Almuerzo Ejecutivo',
            'description' => 'Plato principal + Bebida',
            'regular_price' => 45.00,
            'price' => 35.00,
            'discount_percentage' => 22.22,
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'is_active' => true,
        ]);
        
        // Add items
        $combo->items()->create([
            'product_template_id' => $product1->id,
            'quantity' => 1,
            'allow_substitution' => false,
        ]);
        
        $combo->items()->create([
            'product_template_id' => $product2->id,
            'quantity' => 1,
            'allow_substitution' => true,
        ]);
        
        echo "   ✅ Combo created successfully!\n";
        echo "      - Name: {$combo->name}\n";
        echo "      - Regular Price: S/ {$combo->regular_price}\n";
        echo "      - Combo Price: S/ {$combo->price}\n";
        echo "      - Discount: {$combo->discount_percentage}%\n";
        echo "      - Items: " . $combo->items->count() . "\n";
        
        foreach ($combo->items as $item) {
            $sub = $item->allow_substitution ? '(can substitute)' : '';
            echo "        • {$item->quantity}x {$item->product->name} {$sub}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// ============================================
// TEST 4: Get Active Combos
// ============================================
echo "🔍 TEST 4: Getting Active Combos\n";
echo str_repeat("-", 50) . "\n";

try {
    $activeCombos = Combo::active()->with('items.product')->get();
    
    echo "   Found {$activeCombos->count()} active combo(s)\n";
    
    foreach ($activeCombos as $combo) {
        echo "\n   📌 {$combo->name}\n";
        echo "      Price: S/ {$combo->price} (was S/ {$combo->regular_price})\n";
        echo "      Valid until: " . ($combo->end_date ? $combo->end_date->format('Y-m-d') : 'No expiration') . "\n";
        echo "      Items:\n";
        foreach ($combo->items as $item) {
            echo "        • {$item->quantity}x {$item->product->name}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";
echo str_repeat("=", 50) . "\n";
echo "✅ Tests completed!\n\n";
