<?php

namespace App\Http\Controllers;

use App\Http\Resources\StockTransferResource;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class StockTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StockTransfer::with(['fromWarehouse', 'toWarehouse']);

        // Filter by status if requested
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $transfers = $query->latest()->paginate(10);

        return Inertia::render('StockTransfers/Index', [
            'transfers' => StockTransferResource::collection($transfers),
            'statusFilter' => $request->get('status'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $warehouses = Warehouse::active()->get();

        return Inertia::render('StockTransfers/Create', [
            'warehouses' => \App\Http\Resources\WarehouseResource::collection($warehouses),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transfer_number' => 'required|string|max:20|unique:stock_transfers,transfer_number',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => [
                'required',
                'exists:warehouses,id',
                'different:from_warehouse_id',
            ],
            'transfer_date' => 'required|date',
            'status' => ['required', 'string', Rule::in([
                StockTransfer::STATUS_PENDING,
                StockTransfer::STATUS_IN_TRANSIT,
                StockTransfer::STATUS_RECEIVED,
                StockTransfer::STATUS_CANCELLED,
            ])],
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:product_product,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            // Create stock transfer
            $transfer = StockTransfer::create([
                'transfer_number' => $validated['transfer_number'],
                'from_warehouse_id' => $validated['from_warehouse_id'],
                'to_warehouse_id' => $validated['to_warehouse_id'],
                'transfer_date' => $validated['transfer_date'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Create transfer items via productables
            foreach ($validated['items'] as $item) {
                $transfer->productables()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => 0, // Transfers don't have prices
                    'discount' => 0,
                    'tax_amount' => 0,
                    'total' => 0,
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('stock-transfers.index')
            ->with('success', 'Stock transfer created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $transfer = StockTransfer::with(['productables.product.template'])->findOrFail($id);
        $warehouses = Warehouse::active()->get();

        return Inertia::render('StockTransfers/Edit', [
            'transfer' => new StockTransferResource($transfer),
            'warehouses' => \App\Http\Resources\WarehouseResource::collection($warehouses),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $transfer = StockTransfer::findOrFail($id);

        $validated = $request->validate([
            'transfer_number' => ['required', 'string', 'max:20', Rule::unique('stock_transfers')->ignore($transfer->id)],
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => [
                'required',
                'exists:warehouses,id',
                'different:from_warehouse_id',
            ],
            'transfer_date' => 'required|date',
            'status' => ['required', 'string', Rule::in([
                StockTransfer::STATUS_PENDING,
                StockTransfer::STATUS_IN_TRANSIT,
                StockTransfer::STATUS_RECEIVED,
                StockTransfer::STATUS_CANCELLED,
            ])],
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:product_product,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($transfer, $validated) {
            // Update stock transfer
            $transfer->update([
                'transfer_number' => $validated['transfer_number'],
                'from_warehouse_id' => $validated['from_warehouse_id'],
                'to_warehouse_id' => $validated['to_warehouse_id'],
                'transfer_date' => $validated['transfer_date'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Delete existing items and recreate
            $transfer->productables()->delete();

            foreach ($validated['items'] as $item) {
                $transfer->productables()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => 0,
                    'discount' => 0,
                    'tax_amount' => 0,
                    'total' => 0,
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('stock-transfers.index')
            ->with('success', 'Stock transfer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $transfer = StockTransfer::findOrFail($id);
        
        DB::transaction(function () use ($transfer) {
            $transfer->productables()->delete();
            $transfer->delete();
        });

        return redirect()->route('stock-transfers.index')
            ->with('success', 'Stock transfer deleted successfully.');
    }
}
