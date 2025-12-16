<?php

namespace App\Http\Controllers;

use App\Facades\Kardex; // Import Facade
use App\Http\Resources\PurchaseOrderResource;
use App\Models\Company;
use App\Models\Journal;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    // Removed constructor injection

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['partner', 'warehouse', 'branch']);

        // Filter by status if requested
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $orders = $query->latest()->paginate(10);

        return Inertia::render('PurchaseOrders/Index', [
            'orders' => PurchaseOrderResource::collection($orders),
            'statusFilter' => $request->get('status'),
        ]);
    }

    public function create()
    {
        $branches = Company::branches()->active()->get();
        $warehouses = Warehouse::active()->get();
        $taxes = \App\Models\Tax::active()->get();
        $journals = Journal::where('type', 'purchase')->where('is_active', true)->get();

        return Inertia::render('PurchaseOrders/Create', [
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
            'expected_delivery_date' => 'nullable|date',
            'status' => ['required', 'string', Rule::in([
                PurchaseOrder::STATUS_QUOTE_REQUEST,
                PurchaseOrder::STATUS_QUOTE_RECEIVED,
                PurchaseOrder::STATUS_ORDERED,
                PurchaseOrder::STATUS_APPROVED,
                PurchaseOrder::STATUS_RECEIVED,
                PurchaseOrder::STATUS_PAID,
                PurchaseOrder::STATUS_CANCELLED,
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
        $subtotal = collect($validated['items'])->sum('total');
        $tax = collect($validated['items'])->sum('tax_amount');
        $total = $subtotal;

        DB::transaction(function () use ($validated, $subtotal, $tax, $total, $sequence) {
            // Create purchase order with auto-generated order_number
            $order = PurchaseOrder::create([
                'order_number' => $sequence['full_number'], // e.g., "OC-00000001"
                'journal_id' => $validated['journal_id'],
                'branch_id' => $validated['branch_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'partner_id' => $validated['partner_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'status' => $validated['status'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Create order items via productables
            foreach ($validated['items'] as $item) {
                $subtotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
                $order->productables()->create([
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

            // Register Kardex if status is RECEIVED
            if ($order->status === PurchaseOrder::STATUS_RECEIVED) {
                // Reload items to get fresh data if needed, or use validated
                // Using validated items loop for simplicity as we have the data
                foreach ($validated['items'] as $item) {
                    Kardex::registerEntry(
                        $order,
                        [
                            'id' => $item['product_id'],
                            'quantity' => (float) $item['quantity'],
                            'price' => (float) $item['unit_price'],
                            'subtotal' => (float) $item['total'], // Assuming total is subtotal for cost calc
                        ],
                        $order->warehouse_id,
                        "Purchase Order #{$order->order_number}"
                    );
                }
            }
        });

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}

    public function edit(string $id)
    {
        $order = PurchaseOrder::with(['productables.product.template'])->findOrFail($id);
        $branches = Company::branches()->active()->get();
        $warehouses = Warehouse::active()->get();
        $taxes = \App\Models\Tax::active()->get();

        return Inertia::render('PurchaseOrders/Edit', [
            'order' => new PurchaseOrderResource($order),
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
        $order = PurchaseOrder::findOrFail($id);

        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:20', Rule::unique('purchase_orders')->ignore($order->id)],
            'branch_id' => 'required|exists:companies,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'partner_id' => 'required|exists:partners,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'status' => ['required', 'string', Rule::in([
                PurchaseOrder::STATUS_QUOTE_REQUEST,
                PurchaseOrder::STATUS_QUOTE_RECEIVED,
                PurchaseOrder::STATUS_ORDERED,
                PurchaseOrder::STATUS_APPROVED,
                PurchaseOrder::STATUS_RECEIVED,
                PurchaseOrder::STATUS_PAID,
                PurchaseOrder::STATUS_CANCELLED,
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
        $subtotal = collect($validated['items'])->sum('total');
        $tax = collect($validated['items'])->sum('tax_amount');
        $total = $subtotal;

        DB::transaction(function () use ($order, $validated, $subtotal, $tax, $total) {
            $originalStatus = $order->status;

            // Update purchase order
            $order->update([
                'order_number' => $validated['order_number'],
                'branch_id' => $validated['branch_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'partner_id' => $validated['partner_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'status' => $validated['status'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Delete existing items and recreate
            $order->productables()->delete();

            foreach ($validated['items'] as $item) {
                $subtotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
                $order->productables()->create([
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
            // 1. If status changed TO 'received', register input
            if ($originalStatus !== PurchaseOrder::STATUS_RECEIVED && $order->status === PurchaseOrder::STATUS_RECEIVED) {
                foreach ($validated['items'] as $item) {
                    Kardex::registerEntry(
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
                }
            }
            // 2. If status changed FROM 'received' TO 'cancelled', void movement (Exit)
            elseif ($originalStatus === PurchaseOrder::STATUS_RECEIVED && $order->status === PurchaseOrder::STATUS_CANCELLED) {
                // For voiding, we need to know what was previously entered.
                // Since we just deleted items, we might need to rely on the validated items if they match,
                // OR better, we should have fetched items before delete.
                // BUT, since we are in a transaction and just recreated them, we can use the validated items
                // assuming the user didn't change items AND cancel at the same time.
                // Ideally, voiding should be a separate action or we use the items we just saved.

                foreach ($validated['items'] as $item) {
                    Kardex::registerExit(
                        $order,
                        [
                            'id' => $item['product_id'],
                            'quantity' => (float) $item['quantity'],
                            // Price not needed for exit/void usually, but good to have
                        ],
                        $order->warehouse_id,
                        "VOID Purchase #{$order->order_number}"
                    );
                }
            }
        });

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = PurchaseOrder::with('productables')->findOrFail($id);

        DB::transaction(function () use ($order) {
            // If deleting a received order, void movement
            if ($order->status === PurchaseOrder::STATUS_RECEIVED) {
                foreach ($order->productables as $item) {
                    Kardex::registerExit(
                        $order,
                        [
                            'id' => $item->product_id,
                            'quantity' => (float) $item->quantity,
                        ],
                        $order->warehouse_id,
                        "VOID Purchase #{$order->order_number}"
                    );
                }
            }

            $order->productables()->delete();
            $order->delete();
        });

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order deleted successfully.');
    }
}
