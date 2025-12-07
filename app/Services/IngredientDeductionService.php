<?php

namespace App\Services;

use App\Models\ProductTemplate;
use App\Models\Inventory;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class IngredientDeductionService
{
    /**
     * Deduct ingredients from inventory when a dish is sold.
     *
     * @param ProductTemplate $dish The dish/product sold
     * @param int $quantity Quantity of dishes sold
     * @param Warehouse $warehouse Warehouse to deduct from
     * @param int|null $orderId Order ID for traceability
     * @return array Results of the deduction
     */
    public function deductIngredientsForDish(
        ProductTemplate $dish,
        int $quantity,
        Warehouse $warehouse,
        ?int $orderId = null
    ): array {
        $deductions = [];
        $errors = [];

        // Load recipes for the dish
        $recipes = $dish->recipes()->with(['ingredient', 'unit'])->get();

        if ($recipes->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No recipes found for this dish.',
                'deductions' => [],
                'errors' => [],
            ];
        }

        DB::transaction(function () use ($recipes, $quantity, $warehouse, $orderId, &$deductions, &$errors) {
            foreach ($recipes as $recipe) {
                try {
                    // Calculate quantity needed (including waste)
                    $quantityNeeded = $recipe->quantity * $quantity;
                    $wasteAdjusted = $quantityNeeded * (1 + $recipe->waste_percentage / 100);

                    // Get current inventory balance
                    $latestInventory = Inventory::where('product_id', $recipe->ingredient_id)
                        ->where('warehouse_id', $warehouse->id)
                        ->latest()
                        ->first();

                    if (!$latestInventory || $latestInventory->quantity_balance < $wasteAdjusted) {
                        $errors[] = [
                            'ingredient' => $recipe->ingredient->template->name ?? 'Unknown',
                            'required' => $wasteAdjusted,
                            'available' => $latestInventory->quantity_balance ?? 0,
                            'message' => 'Insufficient inventory',
                        ];
                        
                        throw new \Exception('Insufficient inventory for ' . ($recipe->ingredient->template->name ?? 'ingredient'));
                    }

                    // Create inventory movement (deduction)
                    $newBalance = $latestInventory->quantity_balance - $wasteAdjusted;
                    $costPerUnit = $latestInventory->cost_balance;
                    $totalCost = $costPerUnit * $wasteAdjusted;

                    $inventory = Inventory::create([
                        'product_id' => $recipe->ingredient_id,
                        'warehouse_id' => $warehouse->id,
                        'inventoryable_id' => $orderId,
                        'inventoryable_type' => $orderId ? 'App\\Models\\Order' : null,
                        'detail' => "Consumo por venta - {$dish->name}",
                        'quantity_in' => 0,
                        'cost_in' => 0,
                        'total_in' => 0,
                        'quantity_out' => $wasteAdjusted,
                        'cost_out' => $costPerUnit,
                        'total_out' => $totalCost,
                        'quantity_balance' => $newBalance,
                        'cost_balance' => $costPerUnit,
                        'total_balance' => $newBalance * $costPerUnit,
                    ]);

                    $deductions[] = [
                        'ingredient' => $recipe->ingredient->template->name ?? 'Unknown',
                        'quantity_deducted' => $wasteAdjusted,
                        'unit' => $recipe->unit->name,
                        'cost' => $totalCost,
                        'new_balance' => $newBalance,
                        'inventory_id' => $inventory->id,
                    ];

                } catch (\Exception $e) {
                    // Re-throw to rollback transaction
                    throw $e;
                }
            }
        });

        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Failed to deduct ingredients',
                'deductions' => $deductions,
                'errors' => $errors,
            ];
        }

        return [
            'success' => true,
            'message' => 'Ingredients deducted successfully',
            'deductions' => $deductions,
            'errors' => [],
        ];
    }

    /**
     * Check if there's enough inventory for a dish.
     *
     * @param ProductTemplate $dish
     * @param int $quantity
     * @param Warehouse $warehouse
     * @return array
     */
    public function checkInventoryAvailability(
        ProductTemplate $dish,
        int $quantity,
        Warehouse $warehouse
    ): array {
        $recipes = $dish->recipes()->with(['ingredient', 'unit'])->get();
        $availability = [];
        $canFulfill = true;

        foreach ($recipes as $recipe) {
            $quantityNeeded = $recipe->quantity * $quantity;
            $wasteAdjusted = $quantityNeeded * (1 + $recipe->waste_percentage / 100);

            $latestInventory = Inventory::where('product_id', $recipe->ingredient_id)
                ->where('warehouse_id', $warehouse->id)
                ->latest()
                ->first();

            $available = $latestInventory->quantity_balance ?? 0;
            $sufficient = $available >= $wasteAdjusted;

            if (!$sufficient) {
                $canFulfill = false;
            }

            $availability[] = [
                'ingredient' => $recipe->ingredient->template->name ?? 'Unknown',
                'required' => round($wasteAdjusted, 3),
                'available' => round($available, 3),
                'unit' => $recipe->unit->name,
                'sufficient' => $sufficient,
            ];
        }

        return [
            'can_fulfill' => $canFulfill,
            'items' => $availability,
        ];
    }
}
