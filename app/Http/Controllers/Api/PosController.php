<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\TableAreaResource;
use App\Http\Resources\TableResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\ProductCategory;
use App\Models\ProductTemplate;
use App\Models\Table;
use App\Models\TableArea;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    /**
     * Get all tables with their current status
     */
    public function tables(Request $request): JsonResponse
    {
        $query = Table::with(['area', 'branch'])
            ->where('is_active', true);

        // Filter by branch if provided
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by area if provided
        if ($request->has('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        $tables = $query->get();

        return response()->json([
            'success' => true,
            'data' => TableResource::collection($tables),
        ]);
    }

    /**
     * Get table areas
     */
    public function tableAreas(Request $request): JsonResponse
    {
        $query = TableArea::with('branch')->where('is_active', true);

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $areas = $query->get();

        return response()->json([
            'success' => true,
            'data' => TableAreaResource::collection($areas),
        ]);
    }

    /**
     * Get product categories for menu
     */
    public function categories(Request $request): JsonResponse
    {
        $categories = ProductCategory::where('type', 'menu')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Get products for POS
     */
    public function products(Request $request): JsonResponse
    {
        $query = ProductTemplate::with(['menuCategory', 'kitchenStation'])
            ->where('can_be_sold', true)
            ->where('is_active', true);

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('menu_category_id', $request->category_id);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $products = $query->get();

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
        ]);
    }

    /**
     * Get payment methods
     */
    public function paymentMethods(): JsonResponse
    {
        $methods = PaymentMethod::where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => PaymentMethodResource::collection($methods),
        ]);
    }

    /**
     * Create new order
     */
    public function createOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'table_id' => 'nullable|exists:tables,id',
            'guests_count' => 'required|integer|min:1|max:50',
            'order_type' => 'nullable|in:dine_in,takeout,delivery',
            'items' => 'required|array|min:1',
            'items.*.product_template_id' => 'required|exists:product_template,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.special_instructions' => 'nullable|string|max:500',
        ]);

        $order = DB::transaction(function () use ($validated, $request) {
            // Generate order number
            $orderNumber = $this->generateOrderNumber();

            // Calculate totals
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $tax = $subtotal * 0.18; // IGV 18%
            $total = $subtotal + $tax;

            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'branch_id' => $request->user()?->branch_id ?? 1,
                'table_id' => $validated['table_id'] ?? null,
                'waiter_id' => $request->user()?->id ?? 1, // Default to user 1 for dev
                'order_type' => $validated['order_type'] ?? 'dine_in',
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_UNPAID,
                'guests_count' => $validated['guests_count'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'order_date' => now(),
            ]);

            // Create order items
            foreach ($validated['items'] as $itemData) {
                $itemSubtotal = $itemData['quantity'] * $itemData['unit_price'];
                $itemTax = $itemSubtotal * 0.18;

                $order->items()->create([
                    'product_template_id' => $itemData['product_template_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $itemSubtotal,
                    'tax_amount' => $itemTax,
                    'total' => $itemSubtotal + $itemTax,
                    'status' => OrderItem::STATUS_PENDING,
                    'special_instructions' => $itemData['special_instructions'] ?? null,
                    'sent_to_kitchen_at' => now(),
                ]);
            }

            // Generate kitchen tickets
            $order->generateKitchenTickets();

            return $order;
        });

        return response()->json([
            'success' => true,
            'message' => 'Pedido creado exitosamente',
            'data' => new OrderResource($order->load(['items.productTemplate', 'table', 'waiter'])),
        ], 201);
    }

    /**
     * Get order details
     */
    public function showOrder(Order $order): JsonResponse
    {
        $order->load([
            'items.productTemplate.kitchenStation',
            'table.area',
            'waiter',
            'payments.paymentMethod',
            'kitchenTickets.station',
        ]);

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Add items to existing order
     */
    public function addItems(Request $request, Order $order): JsonResponse
    {
        // Validate order can accept new items
        if (in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])) {
            return response()->json([
                'success' => false,
                'message' => 'No se pueden agregar items a una orden completada o cancelada',
            ], 400);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_template_id' => 'required|exists:product_template,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.special_instructions' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($validated, $order) {
            $additionalSubtotal = 0;

            foreach ($validated['items'] as $itemData) {
                $itemSubtotal = $itemData['quantity'] * $itemData['unit_price'];
                $itemTax = $itemSubtotal * 0.18;

                $order->items()->create([
                    'product_template_id' => $itemData['product_template_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $itemSubtotal,
                    'tax_amount' => $itemTax,
                    'total' => $itemSubtotal + $itemTax,
                    'status' => OrderItem::STATUS_PENDING,
                    'special_instructions' => $itemData['special_instructions'] ?? null,
                    'sent_to_kitchen_at' => now(),
                ]);

                $additionalSubtotal += $itemSubtotal;
            }

            // Update order totals and reset status if needed
            $newSubtotal = $order->subtotal + $additionalSubtotal;
            $newTax = $newSubtotal * 0.18;
            $newTotal = $newSubtotal + $newTax;

            $updateData = [
                'subtotal' => $newSubtotal,
                'tax' => $newTax,
                'total' => $newTotal,
            ];

            // If order was 'ready' for payment, reset to pending since new items were added
            if ($order->status === Order::STATUS_READY) {
                $updateData['status'] = Order::STATUS_PENDING;
            }

            $order->update($updateData);

            // Regenerate kitchen tickets for new items
            $order->generateKitchenTickets();
        });

        return response()->json([
            'success' => true,
            'message' => 'Items agregados exitosamente',
            'data' => new OrderResource($order->fresh()->load(['items.productTemplate'])),
        ]);
    }

    /**
     * Remove item from order
     */
    public function removeItem(Order $order, OrderItem $item): JsonResponse
    {
        // Validate item belongs to order
        if ($item->order_id !== $order->id) {
            return response()->json([
                'success' => false,
                'message' => 'Item no pertenece a esta orden',
            ], 400);
        }

        // Validate item can be removed
        if ($item->status === OrderItem::STATUS_SERVED) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un item ya servido',
            ], 400);
        }

        DB::transaction(function () use ($order, $item) {
            // Update order totals
            $newSubtotal = $order->subtotal - $item->subtotal;
            $newTax = $newSubtotal * 0.18;
            $newTotal = $newSubtotal + $newTax;

            $order->update([
                'subtotal' => max($newSubtotal, 0),
                'tax' => max($newTax, 0),
                'total' => max($newTotal, 0),
            ]);

            // Delete item
            $item->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Item eliminado exitosamente',
            'data' => new OrderResource($order->fresh()->load(['items.productTemplate'])),
        ]);
    }

    /**
     * Process payment for order
     */
    public function processPayment(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0',
            'reference_number' => 'nullable|string|max:100',
            'cash_register_id' => 'nullable|exists:cash_registers,id',
        ]);

        // Validate order can accept payment
        if ($order->payment_status === Order::PAYMENT_PAID) {
            return response()->json([
                'success' => false,
                'message' => 'La orden ya está pagada',
            ], 400);
        }

        DB::transaction(function () use ($validated, $order, $request) {
            // Create payment record
            $payment = $order->payments()->create([
                'payment_method_id' => $validated['payment_method_id'],
                'amount' => $validated['amount'],
                'reference_number' => $validated['reference_number'] ?? null,
                'cash_register_id' => $validated['cash_register_id'] ?? null,
                'processed_by' => $request->user()?->id ?? 1, // Default to user 1 for dev
                'payment_date' => now(),
                'status' => 'completed',
            ]);

            // Calculate total paid
            $totalPaid = $order->payments()->sum('amount');

            // Update payment status
            if ($totalPaid >= $order->total) {
                $order->update([
                    'payment_status' => Order::PAYMENT_PAID,
                    'paid_at' => now(),
                    'status' => Order::STATUS_COMPLETED,
                    'completed_time' => now(),
                ]);
            } elseif ($totalPaid > 0) {
                $order->update([
                    'payment_status' => Order::PAYMENT_PARTIAL,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Pago procesado exitosamente',
            'data' => new OrderResource($order->fresh()->load(['payments.paymentMethod'])),
        ]);
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $lastOrder = Order::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastOrder ? (int) substr($lastOrder->order_number, -4) + 1 : 1;

        return 'ORD-'.$date.'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Close order (mark as ready) - Waiter action
     */
    public function closeOrder(Order $order): JsonResponse
    {
        $order->update([
            'status' => 'ready', // Ready for payment
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cuenta cerrada. Lista para pago.',
            'data' => new OrderResource($order->fresh(['table', 'waiter', 'items.productTemplate'])),
        ]);
    }

    /**
     * Get pending payments - Orders ready to pay (Cashier view)
     */
    public function getPendingPayments(): JsonResponse
    {
        $orders = Order::where('status', 'ready')
            ->with(['table.area', 'waiter', 'items.productTemplate'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => OrderResource::collection($orders),
        ]);
    }
}
