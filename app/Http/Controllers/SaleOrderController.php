<?php

namespace App\Http\Controllers;

use App\Facades\Kardex;
use App\Http\Resources\SaleOrderResource;
use App\Models\Company;
use App\Models\SaleOrder;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SaleOrderController extends Controller
{
    // Removed constructor injection

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SaleOrder::with(['partner', 'warehouse', 'branch']);

        // Filter by status if requested
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $orders = $query->latest()->paginate(10);

        return Inertia::render('SaleOrders/Index', [
            'orders' => SaleOrderResource::collection($orders),
            'statusFilter' => $request->get('status'),
        ]);
    }

    // ... create method unchanged ...

    public function create()
    {
        $branches = Company::branches()->active()->get();
        $warehouses = Warehouse::active()->get();
        $taxes = \App\Models\Tax::active()->get();
        $journals = \App\Models\Journal::where('type', 'sale')->where('is_active', true)->get();

        return Inertia::render('SaleOrders/Create', [
            'branches' => \App\Http\Resources\CompanyResource::collection($branches),
            'warehouses' => \App\Http\Resources\WarehouseResource::collection($warehouses),
            'taxes' => $taxes,
            'journals' => $journals,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // order_number se genera automáticamente basado en journal_id
            'journal_id' => 'required|exists:journals,id',
            'branch_id' => 'required|exists:companies,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'partner_id' => 'required|exists:partners,id',
            'order_date' => 'required|date',
            'quote_valid_until' => 'nullable|date',
            'delivery_date' => 'nullable|date',
            'delivery_address' => 'nullable|string|max:255',
            'delivery_contact' => 'nullable|string|max:100',
            'delivery_phone' => 'nullable|string|max:20',
            'status' => ['required', 'string', Rule::in([
                SaleOrder::STATUS_QUOTE,
                SaleOrder::STATUS_QUOTE_SENT,
                SaleOrder::STATUS_APPROVED,
                SaleOrder::STATUS_PROCESSING,
                SaleOrder::STATUS_DELIVERED,
                SaleOrder::STATUS_PAID,
                SaleOrder::STATUS_CANCELLED,
            ])],
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:product_product,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        // Generar order_number automáticamente usando SequenceService
        $sequenceService = app(\App\Services\SequenceService::class);
        $sequence = $sequenceService->getNextNumber($validated['journal_id']);

        // Calculate totals
        $itemsTotal = collect($validated['items'])->sum('total');
        $itemsDiscounts = collect($validated['items'])->sum('discount');
        $subtotal = $itemsTotal + $itemsDiscounts; // Subtotal before discount
        $tax = collect($validated['items'])->sum('tax_amount');
        $total = $itemsTotal;

        DB::transaction(function () use ($validated, $subtotal, $itemsDiscounts, $tax, $total, $sequence) {
            // Create sale order with auto-generated order_number
            $order = SaleOrder::create([
                'order_number' => $sequence['full_number'], // e.g., "F004-00000001"
                'journal_id' => $validated['journal_id'],
                'branch_id' => $validated['branch_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'partner_id' => $validated['partner_id'],
                'order_date' => $validated['order_date'],
                'quote_valid_until' => $validated['quote_valid_until'] ?? null,
                'delivery_date' => $validated['delivery_date'] ?? null,
                'delivery_address' => $validated['delivery_address'] ?? null,
                'delivery_contact' => $validated['delivery_contact'] ?? null,
                'delivery_phone' => $validated['delivery_phone'] ?? null,
                'status' => $validated['status'],
                'subtotal' => $subtotal,
                'discount' => $itemsDiscounts,
                'tax' => $tax,
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Create order items via productables
            foreach ($validated['items'] as $item) {
                $subtotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                    'discount' => $item['discount'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'total' => $item['total'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Register Kardex if status is DELIVERED
            if ($order->status === SaleOrder::STATUS_DELIVERED) {
                foreach ($validated['items'] as $item) {
                    Kardex::registerExit(
                        $order,
                        [
                            'id' => $item['product_id'],
                            'quantity' => (float) $item['quantity'],
                        ],
                        $order->warehouse_id,
                        "Sale Order #{$order->order_number}"
                    );
                }
            }
        });

        return redirect()->route('sale-orders.index')
            ->with('success', 'Sale order created successfully.');
    }

    public function edit(string $id)
    {
        $order = SaleOrder::with(['items.product.template'])->findOrFail($id);
        $branches = Company::branches()->active()->get();
        $warehouses = Warehouse::active()->get();
        $taxes = \App\Models\Tax::active()->get();

        return Inertia::render('SaleOrders/Edit', [
            'order' => new SaleOrderResource($order),
            'branches' => \App\Http\Resources\CompanyResource::collection($branches),
            'warehouses' => \App\Http\Resources\WarehouseResource::collection($warehouses),
            'taxes' => $taxes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $order = SaleOrder::findOrFail($id);

        $validated = $request->validate([
            // journal_id y order_number no se pueden editar después de creación
            'branch_id' => 'required|exists:companies,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'partner_id' => 'required|exists:partners,id',
            'order_date' => 'required|date',
            'quote_valid_until' => 'nullable|date',
            'delivery_date' => 'nullable|date',
            'delivery_address' => 'nullable|string|max:255',
            'delivery_contact' => 'nullable|string|max:100',
            'delivery_phone' => 'nullable|string|max:20',
            'status' => ['required', 'string', Rule::in([
                SaleOrder::STATUS_QUOTE,
                SaleOrder::STATUS_QUOTE_SENT,
                SaleOrder::STATUS_APPROVED,
                SaleOrder::STATUS_PROCESSING,
                SaleOrder::STATUS_DELIVERED,
                SaleOrder::STATUS_PAID,
                SaleOrder::STATUS_CANCELLED,
            ])],
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:product_product,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        // Calculate totals
        $itemsTotal = collect($validated['items'])->sum('total');
        $itemsDiscounts = collect($validated['items'])->sum('discount');
        $subtotal = $itemsTotal + $itemsDiscounts;
        $tax = collect($validated['items'])->sum('tax_amount');
        $total = $itemsTotal;

        DB::transaction(function () use ($order, $validated, $subtotal, $itemsDiscounts, $tax, $total) {
            $originalStatus = $order->status;

            // Update sale order (order_number y journal_id no se modifican)
            $order->update([
                'branch_id' => $validated['branch_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'partner_id' => $validated['partner_id'],
                'order_date' => $validated['order_date'],
                'quote_valid_until' => $validated['quote_valid_until'] ?? null,
                'delivery_date' => $validated['delivery_date'] ?? null,
                'delivery_address' => $validated['delivery_address'] ?? null,
                'delivery_contact' => $validated['delivery_contact'] ?? null,
                'delivery_phone' => $validated['delivery_phone'] ?? null,
                'status' => $validated['status'],
                'subtotal' => $subtotal,
                'discount' => $itemsDiscounts,
                'tax' => $tax,
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Delete existing items and recreate
            $order->items()->delete();

            foreach ($validated['items'] as $item) {
                $subtotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                    'discount' => $item['discount'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'total' => $item['total'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Handle Kardex Logic
            // 1. If status changed TO 'delivered', register output (Exit)
            if ($originalStatus !== SaleOrder::STATUS_DELIVERED && $order->status === SaleOrder::STATUS_DELIVERED) {
                foreach ($validated['items'] as $item) {
                    Kardex::registerExit(
                        $order,
                        [
                            'id' => $item['product_id'],
                            'quantity' => (float) $item['quantity'],
                        ],
                        $order->warehouse_id,
                        "Sale Order #{$order->order_number}"
                    );
                }
            }
            // 2. If status changed FROM 'delivered' TO 'cancelled', void movement
            elseif ($originalStatus === SaleOrder::STATUS_DELIVERED && $order->status === SaleOrder::STATUS_CANCELLED) {
                foreach ($validated['items'] as $item) {
                    // Use registerVoid to return stock using original exit cost
                    Kardex::registerVoid(
                        $order,
                        [
                            'id' => $item['product_id'],
                            'quantity' => (float) $item['quantity'],
                        ],
                        $order->warehouse_id,
                        "VOID Sale #{$order->order_number}"
                    );
                }
            }
        });

        return redirect()->route('sale-orders.index')
            ->with('success', 'Sale order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = SaleOrder::with('items')->findOrFail($id);

        DB::transaction(function () use ($order) {
            // If deleting a delivered order, void movement
            if ($order->status === SaleOrder::STATUS_DELIVERED) {
                foreach ($order->items as $item) {
                    Kardex::registerVoid(
                        $order,
                        [
                            'id' => $item->product_id,
                            'quantity' => (float) $item->quantity,
                        ],
                        $order->warehouse_id,
                        "VOID DELETED Sale #{$order->order_number}"
                    );
                }
            }

            $order->items()->delete();
            $order->delete();
        });

        return redirect()->route('sale-orders.index')
            ->with('success', 'Sale order deleted successfully.');
    }
}
