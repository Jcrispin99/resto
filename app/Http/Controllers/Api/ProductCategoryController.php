<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = ProductCategory::with('children')->whereNull('parent_id')->get();

        return ProductCategoryResource::collection($categories);
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

        $category = ProductCategory::create($validated);

        return new ProductCategoryResource($category);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = ProductCategory::with('children')->findOrFail($id);

        return new ProductCategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = ProductCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'string|max:100',
            'parent_id' => 'nullable|exists:product_categories,id',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return new ProductCategoryResource($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = ProductCategory::findOrFail($id);
        $category->delete();

        return response()->noContent();
    }
}
