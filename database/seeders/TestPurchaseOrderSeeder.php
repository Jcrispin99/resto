<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Partner;
use App\Models\ProductProduct;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestPurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Script para probar la funcionalidad de Purchase Orders y Kardex
     * 
     * Uso:
     * php artisan db:seed --class=TestPurchaseOrderSeeder
     */
    public function run(): void
    {
        echo "\n=== TEST PURCHASE ORDER & KARDEX ===\n\n";

        // 1. Obtener datos existentes
        echo "1. Obteniendo datos existentes...\n";
        
        $warehouse = Warehouse::first();
        if (!$warehouse) {
            echo "   ❌ No hay warehouses. Creando uno...\n";
            $warehouse = Warehouse::create([
                'name' => 'Almacén Principal',
                'code' => 'ALM-01',
                'location' => 'Lima',
                'is_active' => true,
            ]);
        }
        echo "   ✓ Warehouse: {$warehouse->name} (ID: {$warehouse->id})\n";

        $branch = Company::whereNull('parent_id')->first();
        if (!$branch) {
            echo "   ❌ No hay companies. Debes crear una primero.\n";
            return;
        }
        echo "   ✓ Branch: {$branch->name} (ID: {$branch->id})\n";

        $supplier = Partner::suppliers()->first();
        if (!$supplier) {
            echo "   ❌ No hay suppliers. Creando uno...\n";
            $supplier = Partner::create([
                'name' => 'Proveedor Test',
                'partner_type' => Partner::TYPE_COMPANY,
                'is_supplier' => true,
                'is_customer' => false,
                'tax_id' => '20123456789',
                'email' => 'proveedor@test.com',
                'is_active' => true,
            ]);
        }
        echo "   ✓ Supplier: {$supplier->name} (ID: {$supplier->id})\n";

        $products = ProductProduct::take(3)->get();
        if ($products->count() < 1) {
            echo "   ❌ No hay productos. Debes crear productos primero.\n";
            return;
        }
        echo "   ✓ Productos encontrados: {$products->count()}\n";
        foreach ($products as $product) {
            echo "      - {$product->name} (ID: {$product->id})\n";
        }

        // 2. Mostrar estado de inventario ANTES
        echo "\n2. Estado de Inventario ANTES de la compra:\n";
        foreach ($products as $product) {
            $balance = DB::table('inventories')
                ->where('product_id', $product->id)
                ->where('warehouse_id', $warehouse->id)
                ->latest('id')
                ->first();
            
            if ($balance) {
                echo "   - {$product->name}: {$balance->quantity_balance} unidades @ \${$balance->cost_balance} = \${$balance->total_balance}\n";
            } else {
                echo "   - {$product->name}: SIN MOVIMIENTOS (stock inicial: 0)\n";
            }
        }

        // 3. Crear Purchase Order en status RECEIVED
        echo "\n3. Creando Purchase Order...\n";
        
        $orderNumber = 'PO-' . now()->format('ymdHis');
        $items = [];
        $subtotal = 0;

        foreach ($products as $index => $product) {
            $quantity = ($index + 1) * 10; // 10, 20, 30
            $unitPrice = 50 + ($index * 10); // 50, 60, 70
            $total = $quantity * $unitPrice;
            $subtotal += $total;

            $items[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount' => 0,
                'tax_amount' => 0,
                'total' => $total,
                'notes' => "Test item {$product->name}",
            ];

            echo "   - {$product->name}: {$quantity} unidades @ \${$unitPrice} = \${$total}\n";
        }

        echo "   Subtotal: \${$subtotal}\n";

        DB::transaction(function () use ($orderNumber, $branch, $warehouse, $supplier, $items, $subtotal) {
            // Crear la orden
            $order = PurchaseOrder::create([
                'order_number' => $orderNumber,
                'branch_id' => $branch->id,
                'warehouse_id' => $warehouse->id,
                'partner_id' => $supplier->id,
                'order_date' => now(),
                'expected_delivery_date' => now()->addDays(7),
                'status' => PurchaseOrder::STATUS_RECEIVED, // Directamente RECEIVED para afectar Kardex
                'subtotal' => $subtotal,
                'tax' => 0,
                'total' => $subtotal,
                'notes' => 'Orden de prueba para testear Kardex',
                'created_by' => 1, // Asumiendo user ID 1
            ]);

            echo "\n   ✓ Purchase Order creada: {$order->order_number} (ID: {$order->id})\n";

            // Crear productables
            foreach ($items as $item) {
                $subtotalItem = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
                $order->productables()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['unit_price'],
                    'subtotal' => $subtotalItem,
                    'discount' => $item['discount'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'total' => $item['total'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            echo "   ✓ Items creados: " . count($items) . "\n";

            // Registrar en Kardex (simular lo que hace el controller)
            echo "\n4. Registrando movimientos en Kardex...\n";
            foreach ($items as $item) {
                \App\Facades\Kardex::registerEntry(
                    $order,
                    [
                        'id' => $item['product_id'],
                        'quantity' => (float) $item['quantity'],
                        'price' => (float) $item['unit_price'],
                        'subtotal' => (float) $item['total'],
                    ],
                    $order->warehouse_id,
                    "Purchase Order #{$order->order_number}"
                );

                $product = ProductProduct::find($item['product_id']);
                echo "   ✓ Entrada registrada: {$product->name} +{$item['quantity']} unidades\n";
            }
        });

        // 5. Mostrar estado de inventario DESPUÉS
        echo "\n5. Estado de Inventario DESPUÉS de la compra:\n";
        foreach ($products as $product) {
            $balance = DB::table('inventories')
                ->where('product_id', $product->id)
                ->where('warehouse_id', $warehouse->id)
                ->latest('id')
                ->first();
            
            if ($balance) {
                echo "   - {$product->name}: {$balance->quantity_balance} unidades @ \${$balance->cost_balance} = \${$balance->total_balance}\n";
            } else {
                echo "   - {$product->name}: SIN MOVIMIENTOS\n";
            }
        }

        // 6. Mostrar últimos movimientos de Kardex
        echo "\n6. Últimos movimientos en Kardex:\n";
        $recentMovements = DB::table('inventories')
            ->join('product_product', 'inventories.product_id', '=', 'product_product.id')
            ->join('product_template', 'product_product.template_id', '=', 'product_template.id')
            ->where('inventories.warehouse_id', $warehouse->id)
            ->select(
                'inventories.*',
                'product_template.name as product_name',
                'product_product.sku as default_code'
            )
            ->latest('inventories.id')
            ->take(10)
            ->get();

        foreach ($recentMovements as $mov) {
            $type = $mov->quantity_in > 0 ? 'ENTRADA' : 'SALIDA';
            $qty = $mov->quantity_in > 0 ? $mov->quantity_in : $mov->quantity_out;
            $cost = $mov->quantity_in > 0 ? $mov->cost_in : $mov->cost_out;
            
            echo "   [{$type}] {$mov->product_name}: {$qty} @ \${$cost} | Balance: {$mov->quantity_balance} @ \${$mov->cost_balance}\n";
            echo "            Detail: {$mov->detail}\n";
        }

        echo "\n=== TEST COMPLETADO ===\n\n";
    }
}
