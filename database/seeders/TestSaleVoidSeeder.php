<?php

namespace Database\Seeders;

use App\Models\ProductProduct;
use App\Models\SaleOrder;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestSaleVoidSeeder extends Seeder
{
    /**
     * Test the registerVoid functionality for sales
     *
     * This script will:
     * 1. Get a product with existing stock
     * 2. Create a sale and deliver it (registers exit)
     * 3. Cancel the sale (should use registerVoid to return stock at original cost)
     * 4. Verify that the cost didn't change
     *
     * Uso:
     * php artisan db:seed --class=TestSaleVoidSeeder
     */
    public function run(): void
    {
        echo "\n=== TEST SALE VOID (registerVoid) ===\n\n";

        // 1. Get test data
        $warehouse = Warehouse::first();
        if (! $warehouse) {
            echo "❌ No warehouse found\n";

            return;
        }

        $product = ProductProduct::first();
        if (! $product) {
            echo "❌ No products found\n";

            return;
        }

        echo "Testing with:\n";
        echo "  Product: {$product->name} (ID: {$product->id})\n";
        echo "  Warehouse: {$warehouse->name} (ID: {$warehouse->id})\n\n";

        // 2. Check initial balance
        $initialBalance = DB::table('inventories')
            ->where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->latest('id')
            ->first();

        if (! $initialBalance) {
            echo "❌ No inventory movements found. Please run TestPurchaseOrderSeeder first.\n";

            return;
        }

        echo "INITIAL STATE:\n";
        echo "  Quantity: {$initialBalance->quantity_balance}\n";
        echo "  Cost: \${$initialBalance->cost_balance}\n";
        echo "  Total: \${$initialBalance->total_balance}\n\n";

        // 3. Create and deliver a sale
        echo "STEP 1: Creating sale order (status = delivered)...\n";

        $saleQty = 5;
        $salePrice = 150; // Selling at $150 (higher than cost)

        $sale = SaleOrder::create([
            'order_number' => 'SO-TEST-'.now()->format('ymdHis'),
            'branch_id' => 1,
            'warehouse_id' => $warehouse->id,
            'partner_id' => 1,
            'order_date' => now(),
            'status' => SaleOrder::STATUS_DELIVERED,
            'subtotal' => $saleQty * $salePrice,
            'discount' => 0,
            'tax' => 0,
            'total' => $saleQty * $salePrice,
            'created_by' => 1,
        ]);

        $sale->items()->create([
            'product_id' => $product->id,
            'quantity' => $saleQty,
            'price' => $salePrice,
            'subtotal' => $saleQty * $salePrice,
            'discount' => 0,
            'tax_amount' => 0,
            'total' => $saleQty * $salePrice,
        ]);

        // Register exit manually (simulating what the controller does)
        \App\Facades\Kardex::registerExit(
            $sale,
            ['id' => $product->id, 'quantity' => $saleQty],
            $warehouse->id,
            "Sale Order #{$sale->order_number}"
        );

        echo "  ✓ Sale created: {$sale->order_number}\n";
        echo "  ✓ Sold {$saleQty} units @ \${$salePrice} = \$".($saleQty * $salePrice)."\n";

        // Check balance after sale
        $afterSaleBalance = DB::table('inventories')
            ->where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->latest('id')
            ->first();

        echo "\nAFTER SALE:\n";
        echo "  Quantity: {$afterSaleBalance->quantity_balance} (was {$initialBalance->quantity_balance})\n";
        echo "  Cost: \${$afterSaleBalance->cost_balance} (should be same as before)\n";
        echo "  Total: \${$afterSaleBalance->total_balance}\n\n";

        // 4. Cancel the sale using registerVoid
        echo "STEP 2: Cancelling sale (using registerVoid)...\n";

        \App\Facades\Kardex::registerVoid(
            $sale,
            ['id' => $product->id, 'quantity' => $saleQty],
            $warehouse->id,
            "VOID Sale #{$sale->order_number}"
        );

        echo "  ✓ Sale voided\n";

        // Check final balance
        $finalBalance = DB::table('inventories')
            ->where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->latest('id')
            ->first();

        echo "\nFINAL STATE (after void):\n";
        echo "  Quantity: {$finalBalance->quantity_balance} (should match initial: {$initialBalance->quantity_balance})\n";
        echo "  Cost: \${$finalBalance->cost_balance} (should match initial: \${$initialBalance->cost_balance})\n";
        echo "  Total: \${$finalBalance->total_balance} (should match initial: \${$initialBalance->total_balance})\n\n";

        // 5. Verify correctness
        echo "VERIFICATION:\n";
        $qtyMatch = abs($finalBalance->quantity_balance - $initialBalance->quantity_balance) < 0.001;
        $costMatch = abs($finalBalance->cost_balance - $initialBalance->cost_balance) < 0.01;
        $totalMatch = abs($finalBalance->total_balance - $initialBalance->total_balance) < 0.01;

        if ($qtyMatch && $costMatch && $totalMatch) {
            echo "  ✅ SUCCESS: All balances match initial state!\n";
            echo "  ✅ registerVoid correctly used original exit cost\n";
        } else {
            echo "  ❌ MISMATCH DETECTED:\n";
            if (! $qtyMatch) {
                echo "     Quantity: Expected {$initialBalance->quantity_balance}, got {$finalBalance->quantity_balance}\n";
            }
            if (! $costMatch) {
                echo "     Cost: Expected \${$initialBalance->cost_balance}, got \${$finalBalance->cost_balance}\n";
            }
            if (! $totalMatch) {
                echo "     Total: Expected \${$initialBalance->total_balance}, got \${$finalBalance->total_balance}\n";
            }
        }

        // 6. Show recent movements
        echo "\nRECENT MOVEMENTS:\n";
        $movements = DB::table('inventories')
            ->where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->latest('id')
            ->take(3)
            ->get();

        foreach ($movements as $mov) {
            $type = $mov->quantity_in > 0 ? 'IN  ' : 'OUT ';
            $qty = $mov->quantity_in > 0 ? $mov->quantity_in : $mov->quantity_out;
            $cost = $mov->quantity_in > 0 ? $mov->cost_in : $mov->cost_out;
            echo "  [{$type}] {$qty} @ \${$cost} | Balance: {$mov->quantity_balance} @ \${$mov->cost_balance}\n";
            echo "         {$mov->detail}\n";
        }

        echo "\n=== TEST COMPLETED ===\n\n";

        // Clean up the test sale
        $sale->items()->delete();
        $sale->delete();
        echo "Note: Test sale order has been cleaned up.\n\n";
    }
}
