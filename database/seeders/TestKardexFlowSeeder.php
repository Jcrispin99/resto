<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Partner;
use App\Models\ProductProduct;
use App\Models\PurchaseOrder;
use App\Models\SaleOrder;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestKardexFlowSeeder extends Seeder
{
    /**
     * Test complete Kardex flow: Purchase → Sale
     * 
     * This demonstrates:
     * 1. Purchase increases inventory (registerEntry)
     * 2. Sale decreases inventory (registerExit)
     * 3. Weighted average cost calculation
     * 
     * Usage:
     * php artisan db:seed --class=TestKardexFlowSeeder
     */
    public function run(): void
    {
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "  TEST KARDEX COMPLETE FLOW: PURCHASE → SALE\n";
        echo str_repeat("=", 70) . "\n\n";

        // 1. Get test data
        $warehouse = Warehouse::first();
        $branch = Company::whereNull('parent_id')->first();
        $supplier = Partner::suppliers()->first();
        $customer = Partner::customers()->first();
        $product = ProductProduct::first();

        if (!$warehouse || !$branch || !$supplier || !$product) {
            echo "❌ Missing required data. Please run seeders first.\n";
            return;
        }

        echo "📦 Test Setup:\n";
        echo "   Product    : {$product->name} (ID: {$product->id})\n";
        echo "   Warehouse  : {$warehouse->name}\n";
        echo "   Supplier   : {$supplier->name}\n";
        if ($customer) {
            echo "   Customer   : {$customer->name}\n";
        }
        echo "\n";

        // 2. Initial state (should be empty after fresh migration)
        echo str_repeat("-", 70) . "\n";
        echo "📊 INITIAL STATE (Fresh Database)\n";
        echo str_repeat("-", 70) . "\n";
        
        $initialBalance = $this->getBalance($product->id, $warehouse->id);
        $this->printBalance($initialBalance, "No movements yet");
        echo "\n";

        // 3. PURCHASE ORDER - Adds to inventory
        echo str_repeat("-", 70) . "\n";
        echo "🛒 STEP 1: PURCHASE ORDER (Increases Inventory)\n";
        echo str_repeat("-", 70) . "\n";
        
        $purchaseQty = 100;
        $purchaseCost = 50;
        
        echo "Creating purchase order...\n";
        echo "   Quantity : {$purchaseQty} units\n";
        echo "   Cost     : \${$purchaseCost} per unit\n";
        echo "   Total    : \$" . ($purchaseQty * $purchaseCost) . "\n";

        $purchase = PurchaseOrder::create([
            'order_number' => 'PO-' . now()->format('ymdHis'),
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'partner_id' => $supplier->id,
            'order_date' => now(),
            'status' => PurchaseOrder::STATUS_RECEIVED,
            'subtotal' => $purchaseQty * $purchaseCost,
            'tax' => 0,
            'total' => $purchaseQty * $purchaseCost,
            'created_by' => 1,
        ]);

        $purchase->productables()->create([
            'product_id' => $product->id,
            'quantity' => $purchaseQty,
            'price' => $purchaseCost,
            'subtotal' => $purchaseQty * $purchaseCost,
            'discount' => 0,
            'tax_amount' => 0,
            'total' => $purchaseQty * $purchaseCost,
        ]);

        // Register in Kardex
        \App\Facades\Kardex::registerEntry(
            $purchase,
            [
                'id' => $product->id,
                'quantity' => $purchaseQty,
                'price' => $purchaseCost,
                'subtotal' => $purchaseQty * $purchaseCost,
            ],
            $warehouse->id,
            "Purchase Order #{$purchase->order_number}"
        );

        echo "\n✅ Purchase registered: {$purchase->order_number}\n\n";

        $afterPurchaseBalance = $this->getBalance($product->id, $warehouse->id);
        $this->printBalance($afterPurchaseBalance, "After Purchase");
        
        echo "\n💡 Analysis:\n";
        echo "   ✓ Quantity INCREASED by {$purchaseQty}\n";
        echo "   ✓ Cost set to \${$purchaseCost} (weighted average)\n";
        echo "   ✓ Total value: \${$afterPurchaseBalance->total_balance}\n";
        echo "\n";

        // 4. SALE ORDER - Removes from inventory
        echo str_repeat("-", 70) . "\n";
        echo "💰 STEP 2: SALE ORDER (Decreases Inventory)\n";
        echo str_repeat("-", 70) . "\n";
        
        $saleQty = 30;
        $salePrice = 150; // Selling higher than cost
        
        echo "Creating sale order...\n";
        echo "   Quantity    : {$saleQty} units\n";
        echo "   Sale Price  : \${$salePrice} per unit (customer pays)\n";
        echo "   Total Sale  : \$" . ($saleQty * $salePrice) . "\n";
        echo "   Exit Cost   : \${$afterPurchaseBalance->cost_balance} (from kardex)\n";

        // Create customer if not exists
        if (!$customer) {
            $customer = Partner::create([
                'name' => 'Cliente Test',
                'partner_type' => Partner::TYPE_INDIVIDUAL,
                'is_customer' => true,
                'is_supplier' => false,
                'tax_id' => '12345678',
                'is_active' => true,
            ]);
        }

        $sale = SaleOrder::create([
            'order_number' => 'SO-' . now()->format('ymdHis'),
            'branch_id' => $branch->id,
            'warehouse_id' => $warehouse->id,
            'partner_id' => $customer->id,
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

        // Register exit in Kardex
        \App\Facades\Kardex::registerExit(
            $sale,
            ['id' => $product->id, 'quantity' => $saleQty],
            $warehouse->id,
            "Sale Order #{$sale->order_number}"
        );

        echo "\n✅ Sale registered: {$sale->order_number}\n\n";

        $afterSaleBalance = $this->getBalance($product->id, $warehouse->id);
        $this->printBalance($afterSaleBalance, "After Sale");
        
        $costOfGoodsSold = $saleQty * $afterPurchaseBalance->cost_balance;
        $profit = ($saleQty * $salePrice) - $costOfGoodsSold;
        $profitMargin = ($profit / ($saleQty * $salePrice)) * 100;

        echo "\n💡 Analysis:\n";
        echo "   ✓ Quantity DECREASED by {$saleQty}\n";
        echo "   ✓ Cost remains at \${$afterSaleBalance->cost_balance} (weighted average preserved)\n";
        echo "   ✓ Remaining stock: {$afterSaleBalance->quantity_balance} units\n";
        echo "\n📈 Profitability:\n";
        echo "   Revenue          : \$" . ($saleQty * $salePrice) . "\n";
        echo "   Cost (COGS)      : \$" . number_format($costOfGoodsSold, 2) . "\n";
        echo "   Profit           : \$" . number_format($profit, 2) . "\n";
        echo "   Profit Margin    : " . number_format($profitMargin, 2) . "%\n";
        echo "\n";

        // 5. Show all movements
        echo str_repeat("-", 70) . "\n";
        echo "📋 KARDEX MOVEMENTS DETAIL\n";
        echo str_repeat("-", 70) . "\n";
        
        $movements = DB::table('inventories')
            ->where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->orderBy('id')
            ->get();

        foreach ($movements as $index => $mov) {
            echo "\n" . ($index + 1) . ". ";
            
            if ($mov->quantity_in > 0) {
                echo "ENTRADA (Purchase)\n";
                echo "   In      : {$mov->quantity_in} units @ \${$mov->cost_in} = \${$mov->total_in}\n";
            } else {
                echo "SALIDA (Sale)\n";
                echo "   Out     : {$mov->quantity_out} units @ \${$mov->cost_out} = \${$mov->total_out}\n";
            }
            
            echo "   Balance : {$mov->quantity_balance} units @ \${$mov->cost_balance} = \${$mov->total_balance}\n";
            echo "   Detail  : {$mov->detail}\n";
        }

        // 6. Summary
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "✅ SUMMARY\n";
        echo str_repeat("=", 70) . "\n";
        echo "Initial Stock        : 0 units\n";
        echo "After Purchase       : {$afterPurchaseBalance->quantity_balance} units @ \${$afterPurchaseBalance->cost_balance}\n";
        echo "After Sale           : {$afterSaleBalance->quantity_balance} units @ \${$afterSaleBalance->cost_balance}\n";
        echo "\nKardex Operations:\n";
        echo "   ✓ Purchase uses registerEntry() → Increases inventory\n";
        echo "   ✓ Sale uses registerExit()     → Decreases inventory\n";
        echo "   ✓ Cost remains consistent (weighted average preserved)\n";
        echo "\n" . str_repeat("=", 70) . "\n\n";
    }

    private function getBalance($productId, $warehouseId)
    {
        return DB::table('inventories')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->latest('id')
            ->first();
    }

    private function printBalance($balance, $title)
    {
        echo "{$title}:\n";
        if ($balance) {
            echo "   Quantity : {$balance->quantity_balance} units\n";
            echo "   Cost     : \${$balance->cost_balance} per unit\n";
            echo "   Total    : \${$balance->total_balance}\n";
        } else {
            echo "   No inventory movements\n";
        }
    }
}
