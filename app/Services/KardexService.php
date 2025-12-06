<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\ProductProduct;
use Illuminate\Database\Eloquent\Model;

class KardexService
{
    public function getLastRecord($product_id, $warehouse_id)
    {
        $lastRecord = Inventory::where('product_id', $product_id)
            ->where('warehouse_id', $warehouse_id)
            ->latest('id')
            ->first();

        return [
            'quantity' => $lastRecord?->quantity_balance ?? 0,
            'cost' => $lastRecord?->cost_balance ?? 0,
            'total' => $lastRecord?->total_balance ?? 0,
            'date' => $lastRecord?->created_at ?? null,
        ];
    }

    public function registerEntry(Model $model, array $product, $warehouse_id, $detail)
    {
        $lastRecord = $this->getLastRecord($product['id'], $warehouse_id);

        $qty = (float) ($product['quantity'] ?? 0);

        // Normalize unit cost: if 'subtotal' is provided and > 0, use it to calculate unit cost
        $baseSubtotal = $product['subtotal'] ?? null;
        $unitCost = ($baseSubtotal !== null && (float) $baseSubtotal > 0 && $qty > 0)
            ? ((float) $baseSubtotal / $qty)
            : (float) ($product['price'] ?? 0);

        $newQuantityBalance = $lastRecord['quantity'] + $qty;
        $newTotalBalance = $lastRecord['total'] + ($qty * $unitCost);
        $newCostBalance = $newQuantityBalance > 0 ? $newTotalBalance / $newQuantityBalance : 0;

        Inventory::create([
            'detail' => $detail,
            'quantity_in' => $qty,
            'cost_in' => $unitCost,
            'total_in' => $qty * $unitCost,
            'quantity_balance' => $newQuantityBalance,
            'cost_balance' => $newCostBalance,
            'total_balance' => $newTotalBalance,
            'product_id' => $product['id'],
            'warehouse_id' => $warehouse_id,
            'inventoryable_type' => get_class($model),
            'inventoryable_id' => $model->id,
        ]);

        // Update stock in ProductProduct if needed (optional, but good for quick access)
        // ProductProduct::where('id', $product['id'])->increment('stock', $qty);
        // Note: User's example had Variant::increment. We can add this if ProductProduct has a stock column.
        // For now, I'll stick to creating the inventory record as that's the source of truth.
    }

    public function registerExit(Model $model, array $product, $warehouse_id, $detail)
    {
        $lastRecord = $this->getLastRecord($product['id'], $warehouse_id);

        $qty = (float) ($product['quantity'] ?? 0);

        $newQuantityBalance = $lastRecord['quantity'] - $qty;

        // For exit, we use the weighted average cost from the last record
        $costOut = $lastRecord['cost'];
        $totalOut = $qty * $costOut;

        $newTotalBalance = $lastRecord['total'] - $totalOut;

        // Cost balance remains the same on exit, unless balance is 0
        $newCostBalance = $newQuantityBalance > 0 ? $newTotalBalance / $newQuantityBalance : $lastRecord['cost'];

        Inventory::create([
            'detail' => $detail,
            'quantity_out' => $qty,
            'cost_out' => $costOut,
            'total_out' => $totalOut,
            'quantity_balance' => $newQuantityBalance,
            'cost_balance' => $newCostBalance,
            'total_balance' => $newTotalBalance,
            'product_id' => $product['id'],
            'warehouse_id' => $warehouse_id,
            'inventoryable_type' => get_class($model),
            'inventoryable_id' => $model->id,
        ]);

        // ProductProduct::where('id', $product['id'])->decrement('stock', $qty);
    }

    /**
     * Register a void/reversal of a previous exit movement.
     * This is used to reverse sales or exits without affecting weighted average cost.
     * It uses the original exit cost instead of sale price.
     *
     * @param Model $model The model being voided (SaleOrder, etc.)
     * @param array $product Product data with 'id' and 'quantity'
     * @param int $warehouse_id Warehouse ID
     * @param string $detail Description of the void movement
     * @return void
     */
    public function registerVoid(Model $model, array $product, $warehouse_id, $detail)
    {
        // Try to find the original exit movement for this model and product
        $originalExit = Inventory::where('inventoryable_type', get_class($model))
            ->where('inventoryable_id', $model->id)
            ->where('product_id', $product['id'])
            ->where('warehouse_id', $warehouse_id)
            ->where('quantity_out', '>', 0)
            ->latest('id')
            ->first();

        $lastRecord = $this->getLastRecord($product['id'], $warehouse_id);
        $qty = (float) ($product['quantity'] ?? 0);

        // Use the original exit cost if found, otherwise use current weighted average
        $costIn = $originalExit ? (float) $originalExit->cost_out : $lastRecord['cost'];

        $newQuantityBalance = $lastRecord['quantity'] + $qty;
        $newTotalBalance = $lastRecord['total'] + ($qty * $costIn);
        $newCostBalance = $newQuantityBalance > 0 ? $newTotalBalance / $newQuantityBalance : $lastRecord['cost'];

        Inventory::create([
            'detail' => $detail,
            'quantity_in' => $qty,
            'cost_in' => $costIn,
            'total_in' => $qty * $costIn,
            'quantity_balance' => $newQuantityBalance,
            'cost_balance' => $newCostBalance,
            'total_balance' => $newTotalBalance,
            'product_id' => $product['id'],
            'warehouse_id' => $warehouse_id,
            'inventoryable_type' => get_class($model),
            'inventoryable_id' => $model->id,
        ]);
    }
}
