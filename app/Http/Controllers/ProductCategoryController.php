<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = ProductCategory::with('parent')->paginate(10);

        return Inertia::render('ProductCategories/Index', [
            'categories' => ProductCategoryResource::collection($categories),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('ProductCategories/Create', [
            'parents' => ProductCategoryResource::collection(ProductCategory::whereNull('parent_id')->get()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:product_categories,id',
            'is_active' => 'boolean',
        ]);

        ProductCategory::create($validated);

        return redirect()->route('product-categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Usually not needed for simple CRUD if Index shows everything, but can be implemented
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = ProductCategory::findOrFail($id);

        return Inertia::render('ProductCategories/Edit', [
            'category' => new ProductCategoryResource($category),
            'parents' => ProductCategoryResource::collection(ProductCategory::whereNull('parent_id')->where('id', '!=', $id)->get()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = ProductCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:product_categories,id',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return redirect()->route('product-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = ProductCategory::findOrFail($id);

        if ($category->children()->exists()) {
            return back()->with('error', 'Cannot delete a category that has subcategories.');
        }

        // Check for products if relationship exists
        // if ($category->products()->exists()) {
        //    return back()->with('error', 'Cannot delete a category that has products.');
        // }

        $category->delete();

        return redirect()->route('product-categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
