<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Models\ProductTemplate;
use App\Models\Unit;
use App\Services\ProductVariantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of purchasable products (inventory items).
     */
    public function index()
    {
        $templates = ProductTemplate::with(['category', 'unit'])
            ->where('can_be_purchased', true)
            ->latest()
            ->paginate(10);

        return Inertia::render('Inventory/Products/Index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return Inertia::render('Inventory/Products/Create', [
            'attributes' => ProductAttribute::with('values')->get(),
            'categories' => ProductCategory::inventory()->get(),
            'units' => Unit::all(),
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request, ProductVariantService $variantService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:product_categories,id',
            'unit_id' => 'required|exists:units,id',
            'product_type' => 'nullable|in:consumable,storable,service',
            'can_be_sold' => 'boolean',
            'can_be_stocked' => 'boolean',
            'sale_price' => 'nullable|numeric|min:0',
            'attribute_lines' => 'array',
            'attribute_lines.*.attribute_id' => 'required|exists:product_attributes,id',
            'attribute_lines.*.value_ids' => 'required|array|min:1',
            'attribute_lines.*.value_ids.*' => 'exists:product_attribute_values,id',
        ]);

        DB::transaction(function () use ($validated, $variantService) {
            $template = ProductTemplate::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'category_id' => $validated['category_id'],
                'unit_id' => $validated['unit_id'],
                'product_type' => $validated['product_type'] ?? 'storable',
                'can_be_sold' => $validated['can_be_sold'] ?? false,
                'can_be_purchased' => true, // Always true for inventory products
                'can_be_stocked' => $validated['can_be_stocked'] ?? true,
                'sale_price' => $validated['sale_price'] ?? 0,
            ]);

            if (!empty($validated['attribute_lines'])) {
                foreach ($validated['attribute_lines'] as $lineData) {
                    $line = $template->attributeLines()->create([
                        'attribute_id' => $lineData['attribute_id'],
                    ]);
                    $line->values()->attach($lineData['value_ids']);
                }
            }

            $variantService->generateVariants($template);
        });

        return redirect()->route('inventory.products.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(ProductTemplate $product)
    {
        return Inertia::render('Inventory/Products/Edit', [
            'template' => $product->load(['attributeLines.values', 'products']),
            'attributes' => ProductAttribute::with('values')->get(),
            'categories' => ProductCategory::inventory()->get(),
            'units' => Unit::all(),
        ]);
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, ProductTemplate $product, ProductVariantService $variantService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:product_categories,id',
            'unit_id' => 'required|exists:units,id',
            'product_type' => 'nullable|in:consumable,storable,service',
            'can_be_sold' => 'boolean',
            'can_be_stocked' => 'boolean',
            'sale_price' => 'nullable|numeric|min:0',
            'attribute_lines' => 'array',
            'attribute_lines.*.attribute_id' => 'required|exists:product_attributes,id',
            'attribute_lines.*.value_ids' => 'required|array|min:1',
            'attribute_lines.*.value_ids.*' => 'exists:product_attribute_values,id',
        ]);

        DB::transaction(function () use ($validated, $product, $variantService) {
            $product->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'category_id' => $validated['category_id'],
                'unit_id' => $validated['unit_id'],
                'product_type' => $validated['product_type'] ?? 'storable',
                'can_be_sold' => $validated['can_be_sold'] ?? false,
                'can_be_purchased' => true, // Always true for inventory products
                'can_be_stocked' => $validated['can_be_stocked'] ?? true,
                'sale_price' => $validated['sale_price'] ?? 0,
            ]);

            $product->attributeLines()->delete();

            if (!empty($validated['attribute_lines'])) {
                foreach ($validated['attribute_lines'] as $lineData) {
                    $line = $product->attributeLines()->create([
                        'attribute_id' => $lineData['attribute_id'],
                    ]);
                    $line->values()->attach($lineData['value_ids']);
                }
            }

            $variantService->generateVariants($product);
        });

        return redirect()->route('inventory.products.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(ProductTemplate $product)
    {
        $product->delete();

        return redirect()->route('inventory.products.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }
}
