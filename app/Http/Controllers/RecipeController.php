<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\ProductTemplate;
use App\Models\ProductProduct;
use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecipeController extends Controller
{
    /**
     * Display recipes for a specific product template.
     */
    public function index(Request $request)
    {
        $productTemplateId = $request->query('product_template_id');
        
        $query = Recipe::with(['productTemplate', 'ingredient.template', 'unit']);
        
        if ($productTemplateId) {
            $query->where('product_template_id', $productTemplateId);
        }
        
        $recipes = $query->latest()->paginate(15);
        
        return Inertia::render('Recipes/Index', [
            'recipes' => $recipes,
            'productTemplates' => ProductTemplate::where('can_be_sold', true)->get(),
            'filters' => $request->only(['product_template_id']),
        ]);
    }

    /**
     * Show the form for creating a new recipe.
     */
    public function create(Request $request)
    {
        $productTemplateId = $request->query('product_template_id');
        
        return Inertia::render('Recipes/Create', [
            'productTemplates' => ProductTemplate::where('can_be_sold', true)->get(),
            'ingredients' => ProductProduct::with('template')->get(),
            'units' => Unit::all(),
            'selectedProductTemplateId' => $productTemplateId,
        ]);
    }

    /**
     * Store a newly created recipe in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_template_id' => 'required|exists:product_template,id',
            'ingredient_id' => 'required|exists:product_product,id',
            'quantity' => 'required|numeric|min:0.001',
            'unit_id' => 'required|exists:units,id',
            'waste_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $recipe = Recipe::create($validated);

        return redirect()
            ->route('recipes.index', ['product_template_id' => $recipe->product_template_id])
            ->with('success', 'Recipe created successfully.');
    }

    /**
     * Show the form for editing the specified recipe.
     */
    public function edit(Recipe $recipe)
    {
        return Inertia::render('Recipes/Edit', [
            'recipe' => $recipe->load(['productTemplate', 'ingredient.template', 'unit']),
            'productTemplates' => ProductTemplate::where('can_be_sold', true)->get(),
            'ingredients' => ProductProduct::with('template')->get(),
            'units' => Unit::all(),
        ]);
    }

    /**
     * Update the specified recipe in storage.
     */
    public function update(Request $request, Recipe $recipe)
    {
        $validated = $request->validate([
            'product_template_id' => 'required|exists:product_template,id',
            'ingredient_id' => 'required|exists:product_product,id',
            'quantity' => 'required|numeric|min:0.001',
            'unit_id' => 'required|exists:units,id',
            'waste_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $recipe->update($validated);

        return redirect()
            ->route('recipes.index', ['product_template_id' => $recipe->product_template_id])
            ->with('success', 'Recipe updated successfully.');
    }

    /**
     * Remove the specified recipe from storage.
     */
    public function destroy(Recipe $recipe)
    {
        $productTemplateId = $recipe->product_template_id;
        $recipe->delete();

        return redirect()
            ->route('recipes.index', ['product_template_id' => $productTemplateId])
            ->with('success', 'Recipe deleted successfully.');
    }

    /**
     * Get total cost for a product template based on its recipes.
     */
    public function calculateCost(ProductTemplate $productTemplate)
    {
        $totalCost = 0;
        
        foreach ($productTemplate->recipes as $recipe) {
            // Get current cost of ingredient from inventory
            $ingredient = $recipe->ingredient;
            $latestInventory = $ingredient->inventories()
                ->latest()
                ->first();
            
            if ($latestInventory) {
                $ingredientCost = $latestInventory->cost_balance;
                $quantityNeeded = $recipe->quantity * (1 + $recipe->waste_percentage / 100);
                $totalCost += $ingredientCost * $quantityNeeded;
            }
        }
        
        return response()->json([
            'product_template_id' => $productTemplate->id,
            'product_name' => $productTemplate->name,
            'total_cost' => round($totalCost, 2),
            'sale_price' => $productTemplate->sale_price,
            'margin' => round($productTemplate->sale_price - $totalCost, 2),
            'margin_percentage' => $totalCost > 0 
                ? round((($productTemplate->sale_price - $totalCost) / $productTemplate->sale_price) * 100, 2)
                : 0,
        ]);
    }
}
