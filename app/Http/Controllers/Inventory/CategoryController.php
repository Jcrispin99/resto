<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display a listing of inventory categories.
     */
    public function index()
    {
        $categories = ProductCategory::where('type', 'inventory')
            ->with('parent')
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('Inventory/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new inventory category.
     */
    public function create()
    {
        $parents = ProductCategory::where('type', 'inventory')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return Inertia::render('Inventory/Categories/Create', [
            'parents' => $parents,
        ]);
    }

    /**
     * Store a newly created inventory category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => [
                'nullable',
                'exists:product_categories,id',
                Rule::exists('product_categories', 'id')->where('type', 'inventory'),
            ],
            'is_active' => 'boolean',
        ]);

        ProductCategory::create([
            ...$validated,
            'type' => 'inventory',
        ]);

        return redirect()->route('inventory.categories.index')
            ->with('success', 'Categoría de inventario creada exitosamente.');
    }

    /**
     * Show the form for editing an inventory category.
     */
    public function edit(ProductCategory $category)
    {
        // Ensure it's an inventory category
        abort_if($category->type !== 'inventory', 404);

        $parents = ProductCategory::where('type', 'inventory')
            ->whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return Inertia::render('Inventory/Categories/Edit', [
            'category' => $category->load('parent'),
            'parents' => $parents,
        ]);
    }

    /**
     * Update an inventory category.
     */
    public function update(Request $request, ProductCategory $category)
    {
        // Ensure it's an inventory category
        abort_if($category->type !== 'inventory', 404);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => [
                'nullable',
                'exists:product_categories,id',
                Rule::exists('product_categories', 'id')->where('type', 'inventory'),
                Rule::notIn([$category->id]), // Prevent self-reference
            ],
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return redirect()->route('inventory.categories.index')
            ->with('success', 'Categoría actualizada exitosamente.');
    }

    /**
     * Remove an inventory category.
     */
    public function destroy(ProductCategory $category)
    {
        // Ensure it's an inventory category
        abort_if($category->type !== 'inventory', 404);

        if ($category->children()->exists()) {
            return back()->with('error', 'No se puede eliminar una categoría que tiene subcategorías.');
        }

        // Check for products
        $productCount = \App\Models\ProductTemplate::where('category_id', $category->id)->count();
        if ($productCount > 0) {
            return back()->with('error', "No se puede eliminar una categoría que tiene {$productCount} productos.");
        }

        $category->delete();

        return redirect()->route('inventory.categories.index')
            ->with('success', 'Categoría eliminada exitosamente.');
    }
}
