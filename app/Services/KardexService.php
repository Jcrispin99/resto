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

        $model->inventories()->create([
            'detail' => $detail,
            'quantity_in' => $qty,
            'cost_in' => $unitCost,
            'total_in' => $qty * $unitCost,
            'quantity_balance' => $newQuantityBalance,
            'cost_balance' => $newCostBalance,
            'total_balance' => $newTotalBalance,
            'product_id' => $product['id'],
            'warehouse_id' => $warehouse_id,
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

        $model->inventories()->create([
            'detail' => $detail,
            'quantity_out' => $qty,
            'cost_out' => $costOut,
            'total_out' => $totalOut,
            'quantity_balance' => $newQuantityBalance,
            'cost_balance' => $newCostBalance,
            'total_balance' => $newTotalBalance,
            'product_id' => $product['id'],
            'warehouse_id' => $warehouse_id,
        ]);

        // ProductProduct::where('id', $product['id'])->decrement('stock', $qty);
    }
}
