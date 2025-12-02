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
            'categories' => \App\Models\ProductCategory::all(),
            'units' => \App\Models\Unit::all(),
        ]);
    }

    public function store(Request $request, \App\Services\ProductVariantService $variantService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:product_categories,id',
            'unit_id' => 'required|exists:units,id',
            'attribute_lines' => 'array',
            'attribute_lines.*.attribute_id' => 'required|exists:product_attributes,id',
            'attribute_lines.*.value_ids' => 'required|array|min:1',
            'attribute_lines.*.value_ids.*' => 'exists:product_attribute_values,id',
        ]);

        DB::transaction(function () use ($validated, $variantService) {
            $template = ProductTemplate::create([
                'name' => $validated['name'],
                'category_id' => $validated['category_id'],
                'unit_id' => $validated['unit_id'],
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
        });

        return redirect()->route('product-templates.index');
    }

    public function edit(ProductTemplate $productTemplate)
    {
        return Inertia::render('ProductTemplates/Edit', [
            'template' => $productTemplate->load(['attributeLines.values']),
            'attributes' => ProductAttribute::with('values')->get(),
            'categories' => \App\Models\ProductCategory::all(),
            'units' => \App\Models\Unit::all(),
        ]);
    }

    public function update(Request $request, ProductTemplate $productTemplate, \App\Services\ProductVariantService $variantService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:product_categories,id',
            'unit_id' => 'required|exists:units,id',
            'attribute_lines' => 'array',
            'attribute_lines.*.attribute_id' => 'required|exists:product_attributes,id',
            'attribute_lines.*.value_ids' => 'required|array|min:1',
            'attribute_lines.*.value_ids.*' => 'exists:product_attribute_values,id',
        ]);

        DB::transaction(function () use ($validated, $productTemplate, $variantService) {
            $productTemplate->update([
                'name' => $validated['name'],
                'category_id' => $validated['category_id'],
                'unit_id' => $validated['unit_id'],
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
        });

        return redirect()->route('product-templates.index');
    }
}
