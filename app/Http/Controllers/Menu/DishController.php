<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Models\ProductTemplate;
use App\Models\Unit;
use App\Services\ProductVariantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DishController extends Controller
{
    /**
     * Display a listing of sellable products (menu items).
     */
    public function index()
    {
        $templates = ProductTemplate::with(['menuCategory', 'unit'])
            ->where('can_be_sold', true)
            ->latest()
            ->paginate(10);

        return Inertia::render('Menu/Dishes/Index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Show the form for creating a new dish.
     */
    public function create()
    {
        return Inertia::render('Menu/Dishes/Create', [
            'attributes' => ProductAttribute::with('values')->get(),
            'menuCategories' => ProductCategory::menu()->get(),
            'inventoryCategories' => ProductCategory::inventory()->get(),
            'units' => Unit::all(),
        ]);
    }

    /**
     * Store a newly created dish.
     */
    public function store(Request $request, ProductVariantService $variantService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'menu_category_id' => 'required|exists:product_categories,id',
            'category_id' => 'nullable|exists:product_categories,id',
            'unit_id' => 'required|exists:units,id',
            'product_type' => 'nullable|in:consumable,storable,service,combo',
            'can_be_purchased' => 'boolean',
            'can_be_stocked' => 'boolean',
            'sale_price' => 'required|numeric|min:0',
            'attribute_lines' => 'array',
            'attribute_lines.*.attribute_id' => 'required|exists:product_attributes,id',
            'attribute_lines.*.value_ids' => 'required|array|min:1',
            'attribute_lines.*.value_ids.*' => 'exists:product_attribute_values,id',
            'menu_settings' => 'nullable|array',
        ]);

        DB::transaction(function () use ($validated, $variantService) {
            $template = ProductTemplate::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'menu_category_id' => $validated['menu_category_id'],
                'category_id' => $validated['category_id'] ?? null,
                'unit_id' => $validated['unit_id'],
                'product_type' => $validated['product_type'] ?? 'consumable',
                'can_be_sold' => true, // Always true for menu items
                'can_be_purchased' => $validated['can_be_purchased'] ?? false,
                'can_be_stocked' => $validated['can_be_stocked'] ?? false,
                'sale_price' => $validated['sale_price'],
            ]);

            if (! empty($validated['attribute_lines'])) {
                foreach ($validated['attribute_lines'] as $lineData) {
                    $line = $template->attributeLines()->create([
                        'attribute_id' => $lineData['attribute_id'],
                    ]);
                    $line->values()->attach($lineData['value_ids']);
                }
            }

            $variantService->generateVariants($template);

            // Create menu settings if provided
            if (! empty($validated['menu_settings']) && ! empty($validated['menu_settings']['enabled'])) {
                $template->menuSettings()->create([
                    'menu_name' => $validated['menu_settings']['menu_name'] ?? null,
                    'menu_description' => $validated['menu_settings']['menu_description'] ?? null,
                    'preparation_time_minutes' => $validated['menu_settings']['preparation_time_minutes'] ?? 0,
                    'is_featured' => $validated['menu_settings']['is_featured'] ?? false,
                    'calories' => $validated['menu_settings']['calories'] ?? null,
                    'is_spicy' => $validated['menu_settings']['is_spicy'] ?? false,
                    'is_vegetarian' => $validated['menu_settings']['is_vegetarian'] ?? false,
                    'is_vegan' => $validated['menu_settings']['is_vegan'] ?? false,
                    'is_gluten_free' => $validated['menu_settings']['is_gluten_free'] ?? false,
                    'allergens' => $validated['menu_settings']['allergens'] ?? [],
                    'available_for_dine_in' => $validated['menu_settings']['available_for_dine_in'] ?? true,
                    'available_for_takeout' => $validated['menu_settings']['available_for_takeout'] ?? true,
                    'available_for_delivery' => $validated['menu_settings']['available_for_delivery'] ?? true,
                ]);
            }
        });

        return redirect()->route('menu.dishes.index')
            ->with('success', 'Plato creado exitosamente.');
    }

    /**
     * Show the form for editing the specified dish.
     */
    public function edit(ProductTemplate $dish)
    {
        return Inertia::render('Menu/Dishes/Edit', [
            'template' => $dish->load(['attributeLines.values', 'menuSettings', 'products']),
            'attributes' => ProductAttribute::with('values')->get(),
            'menuCategories' => ProductCategory::menu()->get(),
            'inventoryCategories' => ProductCategory::inventory()->get(),
            'units' => Unit::all(),
        ]);
    }

    /**
     * Update the specified dish.
     */
    public function update(Request $request, ProductTemplate $dish, ProductVariantService $variantService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'menu_category_id' => 'required|exists:product_categories,id',
            'category_id' => 'nullable|exists:product_categories,id',
            'unit_id' => 'required|exists:units,id',
            'product_type' => 'nullable|in:consumable,storable,service,combo',
            'can_be_purchased' => 'boolean',
            'can_be_stocked' => 'boolean',
            'sale_price' => 'required|numeric|min:0',
            'attribute_lines' => 'array',
            'attribute_lines.*.attribute_id' => 'required|exists:product_attributes,id',
            'attribute_lines.*.value_ids' => 'required|array|min:1',
            'attribute_lines.*.value_ids.*' => 'exists:product_attribute_values,id',
            'menu_settings' => 'nullable|array',
        ]);

        DB::transaction(function () use ($validated, $dish, $variantService) {
            $dish->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'menu_category_id' => $validated['menu_category_id'],
                'category_id' => $validated['category_id'] ?? null,
                'unit_id' => $validated['unit_id'],
                'product_type' => $validated['product_type'] ?? 'consumable',
                'can_be_sold' => true,
                'can_be_purchased' => $validated['can_be_purchased'] ?? false,
                'can_be_stocked' => $validated['can_be_stocked'] ?? false,
                'sale_price' => $validated['sale_price'],
            ]);

            $dish->attributeLines()->delete();

            if (! empty($validated['attribute_lines'])) {
                foreach ($validated['attribute_lines'] as $lineData) {
                    $line = $dish->attributeLines()->create([
                        'attribute_id' => $lineData['attribute_id'],
                    ]);
                    $line->values()->attach($lineData['value_ids']);
                }
            }

            $variantService->generateVariants($dish);

            // Update or create menu settings
            if (! empty($validated['menu_settings']) && ! empty($validated['menu_settings']['enabled'])) {
                $dish->menuSettings()->updateOrCreate(
                    ['product_template_id' => $dish->id],
                    [
                        'menu_name' => $validated['menu_settings']['menu_name'] ?? null,
                        'menu_description' => $validated['menu_settings']['menu_description'] ?? null,
                        'preparation_time_minutes' => $validated['menu_settings']['preparation_time_minutes'] ?? 0,
                        'is_featured' => $validated['menu_settings']['is_featured'] ?? false,
                        'calories' => $validated['menu_settings']['calories'] ?? null,
                        'is_spicy' => $validated['menu_settings']['is_spicy'] ?? false,
                        'is_vegetarian' => $validated['menu_settings']['is_vegetarian'] ?? false,
                        'is_vegan' => $validated['menu_settings']['is_vegan'] ?? false,
                        'is_gluten_free' => $validated['menu_settings']['is_gluten_free'] ?? false,
                        'allergens' => $validated['menu_settings']['allergens'] ?? [],
                        'available_for_dine_in' => $validated['menu_settings']['available_for_dine_in'] ?? true,
                        'available_for_takeout' => $validated['menu_settings']['available_for_takeout'] ?? true,
                        'available_for_delivery' => $validated['menu_settings']['available_for_delivery'] ?? true,
                    ]
                );
            } elseif ($dish->menuSettings) {
                $dish->menuSettings()->delete();
            }
        });

        return redirect()->route('menu.dishes.index')
            ->with('success', 'Plato actualizado exitosamente.');
    }

    /**
     * Remove the specified dish.
     */
    public function destroy(ProductTemplate $dish)
    {
        $dish->delete();

        return redirect()->route('menu.dishes.index')
            ->with('success', 'Plato eliminado exitosamente.');
    }
}
