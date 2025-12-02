<?php

namespace App\Http\Controllers;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProductAttributeController extends Controller
{
    public function index()
    {
        $attributes = ProductAttribute::with('values')->latest()->get();
        
        return Inertia::render('ProductAttributes/Index', [
            'attributes' => $attributes
        ]);
    }

    public function create()
    {
        return Inertia::render('ProductAttributes/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:product_attributes,name',
            'values' => 'array|min:1',
            'values.*.value' => 'required|string|max:100',
        ]);

        DB::transaction(function () use ($validated) {
            $attribute = ProductAttribute::create([
                'name' => $validated['name'],
            ]);

            if (!empty($validated['values'])) {
                foreach ($validated['values'] as $valueData) {
                    $attribute->values()->create([
                        'value' => $valueData['value'],
                    ]);
                }
            }
        });

        return redirect()->route('product-attributes.index')
            ->with('success', 'Attribute created successfully.');
    }

    public function show(ProductAttribute $productAttribute)
    {
        // Not needed for this CRUD
    }

    public function edit(ProductAttribute $productAttribute)
    {
        return Inertia::render('ProductAttributes/Edit', [
            'attribute' => $productAttribute->load('values')
        ]);
    }

    public function update(Request $request, ProductAttribute $productAttribute)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('product_attributes')->ignore($productAttribute->id)],
            'values' => 'array',
            'values.*.id' => 'nullable|exists:product_attribute_values,id',
            'values.*.value' => 'required|string|max:100',
        ]);

        DB::transaction(function () use ($validated, $productAttribute) {
            $productAttribute->update([
                'name' => $validated['name'],
            ]);

            // Sync values
            $existingIds = $productAttribute->values()->pluck('id')->toArray();
            $incomingIds = [];

            if (!empty($validated['values'])) {
                foreach ($validated['values'] as $valueData) {
                    if (isset($valueData['id']) && in_array($valueData['id'], $existingIds)) {
                        // Update existing
                        $value = ProductAttributeValue::find($valueData['id']);
                        $value->update([
                            'value' => $valueData['value'],
                        ]);
                        $incomingIds[] = $value->id;
                    } else {
                        // Create new
                        $newValue = $productAttribute->values()->create([
                            'value' => $valueData['value'],
                        ]);
                        $incomingIds[] = $newValue->id;
                    }
                }
            }

            // Delete removed values
            $toDelete = array_diff($existingIds, $incomingIds);
            ProductAttributeValue::destroy($toDelete);
        });

        return redirect()->route('product-attributes.index')
            ->with('success', 'Attribute updated successfully.');
    }

    public function destroy(ProductAttribute $productAttribute)
    {
        $productAttribute->delete();

        return redirect()->route('product-attributes.index')
            ->with('success', 'Attribute deleted successfully.');
    }
}
