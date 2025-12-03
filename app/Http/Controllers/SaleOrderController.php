<?php

namespace App\Http\Controllers;

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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = Company::branches()->active()->get();
        $warehouses = Warehouse::active()->get();

        return Inertia::render('SaleOrders/Create', [
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
            'order_number' => 'required|string|max:20|unique:sale_orders,order_number',
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
        $subtotal = $itemsTotal + $itemsDiscounts; // Subtotal before discount
        $tax = collect($validated['items'])->sum('tax_amount');
        $total = $itemsTotal;

        DB::transaction(function () use ($validated, $subtotal, $itemsDiscounts, $tax, $total) {
            // Create sale order
            $order = SaleOrder::create([
                'order_number' => $validated['order_number'],
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
                $order->items()->create([
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

        return redirect()->route('sale-orders.index')
            ->with('success', 'Sale order created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $order = SaleOrder::with(['items.product.template'])->findOrFail($id);
        $branches = Company::branches()->active()->get();
        $warehouses = Warehouse::active()->get();

        return Inertia::render('SaleOrders/Edit', [
            'order' => new SaleOrderResource($order),
            'branches' => \App\Http\Resources\CompanyResource::collection($branches),
            'warehouses' => \App\Http\Resources\WarehouseResource::collection($warehouses),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $order = SaleOrder::findOrFail($id);

        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:20', Rule::unique('sale_orders')->ignore($order->id)],
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
            // Update sale order
            $order->update([
                'order_number' => $validated['order_number'],
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
                $order->items()->create([
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

        return redirect()->route('sale-orders.index')
            ->with('success', 'Sale order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = SaleOrder::findOrFail($id);
        
        DB::transaction(function () use ($order) {
            $order->items()->delete();
            $order->delete();
        });

        return redirect()->route('sale-orders.index')
            ->with('success', 'Sale order deleted successfully.');
    }
}
