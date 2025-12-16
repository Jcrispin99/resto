<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display a listing of menu categories.
     */
    public function index()
    {
        $categories = ProductCategory::where('type', 'menu')
            ->with('parent')
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('Menu/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new menu category.
     */
    public function create()
    {
        $parents = ProductCategory::where('type', 'menu')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return Inertia::render('Menu/Categories/Create', [
            'parents' => $parents,
        ]);
    }

    /**
     * Store a newly created menu category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => [
                'nullable',
                'exists:product_categories,id',
                Rule::exists('product_categories', 'id')->where('type', 'menu'),
            ],
            'is_active' => 'boolean',
        ]);

        ProductCategory::create([
            ...$validated,
            'type' => 'menu',
        ]);

        return redirect()->route('menu.categories.index')
            ->with('success', 'Categoría de menú creada exitosamente.');
    }

    /**
     * Show the form for editing a menu category.
     */
    public function edit(ProductCategory $category)
    {
        // Ensure it's a menu category
        abort_if($category->type !== 'menu', 404);

        $parents = ProductCategory::where('type', 'menu')
            ->whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return Inertia::render('Menu/Categories/Edit', [
            'category' => $category->load('parent'),
            'parents' => $parents,
        ]);
    }

    /**
     * Update a menu category.
     */
    public function update(Request $request, ProductCategory $category)
    {
        // Ensure it's a menu category
        abort_if($category->type !== 'menu', 404);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => [
                'nullable',
                'exists:product_categories,id',
                Rule::exists('product_categories', 'id')->where('type', 'menu'),
                Rule::notIn([$category->id]), // Prevent self-reference
            ],
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return redirect()->route('menu.categories.index')
            ->with('success', 'Categoría actualizada exitosamente.');
    }

    /**
     * Remove a menu category.
     */
    public function destroy(ProductCategory $category)
    {
        // Ensure it's a menu category
        abort_if($category->type !== 'menu', 404);

        if ($category->children()->exists()) {
            return back()->with('error', 'No se puede eliminar una categoría que tiene subcategorías.');
        }

        // Check for products
        $productCount = \App\Models\ProductTemplate::where('menu_category_id', $category->id)->count();
        if ($productCount > 0) {
            return back()->with('error', "No se puede eliminar una categoría que tiene {$productCount} platos.");
        }

        $category->delete();

        return redirect()->route('menu.categories.index')
            ->with('success', 'Categoría eliminada exitosamente.');
    }
}
