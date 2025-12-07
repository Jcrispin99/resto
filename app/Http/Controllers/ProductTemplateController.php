<?php

namespace App\Http\Controllers;

use App\Models\ProductAttribute;
use App\Models\ProductTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProductTemplateController extends Controller
{
    public function index()
    {
        $templates = ProductTemplate::with(['category', 'unit'])->latest()->paginate(10);

        return Inertia::render('ProductTemplates/Index', [
            'templates' => $templates,
        ]);
    }

    public function create()
    {
        return Inertia::render('ProductTemplates/Create', [
            'attributes' => ProductAttribute::with('values')->get(),
            'categories' => \App\Models\ProductCategory::inventory()->get(),
            'menuCategories' => \App\Models\ProductCategory::menu()->get(),
            'units' => \App\Models\Unit::all(),
        ]);
    }

    public function store(Request $request, \App\Services\ProductVariantService $variantService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:product_categories,id',
            'menu_category_id' => 'nullable|exists:product_categories,id',
            'unit_id' => 'required|exists:units,id',
            'product_type' => 'nullable|in:consumable,storable,service,combo',
            'can_be_sold' => 'boolean',
            'can_be_purchased' => 'boolean',
            'can_be_stocked' => 'boolean',
            'sale_price' => 'nullable|numeric|min:0',
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
                'category_id' => $validated['category_id'],
                'menu_category_id' => $validated['menu_category_id'] ?? null,
                'unit_id' => $validated['unit_id'],
                'product_type' => $validated['product_type'] ?? 'storable',
                'can_be_sold' => $validated['can_be_sold'] ?? false,
                'can_be_purchased' => $validated['can_be_purchased'] ?? true,
                'can_be_stocked' => $validated['can_be_stocked'] ?? true,
                'sale_price' => $validated['sale_price'] ?? 0,
            ]);

            if (! empty($validated['attribute_lines'])) {
                foreach ($validated['attribute_lines'] as $lineData) {
                    $line = $template->attributeLines()->create([
                        'attribute_id' => $lineData['attribute_id'],
                    ]);
                    $line->values()->attach($lineData['value_ids']);
                }
            }

            // Generate variants
            $variantService->generateVariants($template);

            // Create menu settings if provided
            if (!empty($validated['menu_settings']) && !empty($validated['menu_settings']['enabled'])) {
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

        return redirect()->route('product-templates.index');
    }

    public function edit(ProductTemplate $productTemplate)
    {
        return Inertia::render('ProductTemplates/Edit', [
            'template' => $productTemplate->load(['attributeLines.values', 'menuSettings']),
            'attributes' => ProductAttribute::with('values')->get(),
            'categories' => \App\Models\ProductCategory::inventory()->get(),
            'menuCategories' => \App\Models\ProductCategory::menu()->get(),
            'units' => \App\Models\Unit::all(),
        ]);
    }

    public function update(Request $request, ProductTemplate $productTemplate, \App\Services\ProductVariantService $variantService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:product_categories,id',
            'menu_category_id' => 'nullable|exists:product_categories,id',
            'unit_id' => 'required|exists:units,id',
            'product_type' => 'nullable|in:consumable,storable,service,combo',
            'can_be_sold' => 'boolean',
            'can_be_purchased' => 'boolean',
            'can_be_stocked' => 'boolean',
            'sale_price' => 'nullable|numeric|min:0',
            'attribute_lines' => 'array',
            'attribute_lines.*.attribute_id' => 'required|exists:product_attributes,id',
            'attribute_lines.*.value_ids' => 'required|array|min:1',
            'attribute_lines.*.value_ids.*' => 'exists:product_attribute_values,id',
            'menu_settings' => 'nullable|array',
        ]);

        DB::transaction(function () use ($validated, $productTemplate, $variantService) {
            $productTemplate->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'category_id' => $validated['category_id'],
                'menu_category_id' => $validated['menu_category_id'] ?? null,
                'unit_id' => $validated['unit_id'],
                'product_type' => $validated['product_type'] ?? 'storable',
                'can_be_sold' => $validated['can_be_sold'] ?? false,
                'can_be_purchased' => $validated['can_be_purchased'] ?? true,
                'can_be_stocked' => $validated['can_be_stocked'] ?? true,
                'sale_price' => $validated['sale_price'] ?? 0,
            ]);

            // Sync attribute lines
            $productTemplate->attributeLines()->delete();

            if (! empty($validated['attribute_lines'])) {
                foreach ($validated['attribute_lines'] as $lineData) {
                    $line = $productTemplate->attributeLines()->create([
                        'attribute_id' => $lineData['attribute_id'],
                    ]);
                    $line->values()->attach($lineData['value_ids']);
                }
            }

            // Generate/Update variants
            $variantService->generateVariants($productTemplate);

            // Update or create menu settings
            if (!empty($validated['menu_settings']) && !empty($validated['menu_settings']['enabled'])) {
                $productTemplate->menuSettings()->updateOrCreate(
                    ['product_template_id' => $productTemplate->id],
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
            } elseif ($productTemplate->menuSettings) {
                // Delete menu settings if disabled
                $productTemplate->menuSettings()->delete();
            }
        });

        return redirect()->route('product-templates.index');
    }
}
