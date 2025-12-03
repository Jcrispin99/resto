<?php

namespace App\Http\Controllers;

use App\Http\Resources\PurchaseOrderResource;
use App\Models\Company;
use App\Models\Partner;
use App\Models\ProductProduct;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
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

        return Inertia::render('PurchaseOrders/Create', [
            'branches' => \App\Http\Resources\CompanyResource::collection($branches),
            'warehouses' => \App\Http\Resources\WarehouseResource::collection($warehouses),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|string|max:20|unique:purchase_orders,order_number',
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

        DB::transaction(function () use ($validated, $subtotal, $tax, $total) {
            // Create purchase order
            $order = PurchaseOrder::create([
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
                'created_by' => auth()->id(),
            ]);

            // Create order items via productables
            foreach ($validated['items'] as $item) {
                $order->productables()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'total' => $item['total'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = PurchaseOrder::with(['partner', 'warehouse', 'branch', 'productables.product.template'])
            ->findOrFail($id);

        return Inertia::render('PurchaseOrders/Show', [
            'order' => new PurchaseOrderResource($order),
        ]);
    }

    public function edit(string $id)
    {
        $order = PurchaseOrder::with(['productables.product.template'])->findOrFail($id);
        $branches = Company::branches()->active()->get();
        $warehouses = Warehouse::active()->get();

        return Inertia::render('PurchaseOrders/Edit', [
            'order' => new PurchaseOrderResource($order),
            'branches' => \App\Http\Resources\CompanyResource::collection($branches),
            'warehouses' => \App\Http\Resources\WarehouseResource::collection($warehouses),
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
                $order->productables()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'total' => $item['total'],
                    'notes' => $item['notes'] ?? null,
                ]);
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
        $order = PurchaseOrder::findOrFail($id);
        
        DB::transaction(function () use ($order) {
            $order->productables()->delete();
            $order->delete();
        });

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order deleted successfully.');
    }
}
