<?php

namespace App\Http\Controllers;

use App\Facades\Kardex;
use App\Http\Resources\StockTransferResource;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class StockTransferController extends Controller
{
    // Removed constructor injection

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

    // ... create method unchanged ...

    public function create()
    {
        $warehouses = Warehouse::active()->get();
        $journals = \App\Models\Journal::where('type', 'transfer')->where('is_active', true)->get();

        return Inertia::render('StockTransfers/Create', [
            'warehouses' => \App\Http\Resources\WarehouseResource::collection($warehouses),
            'journals' => $journals,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // transfer_number se genera automáticamente basado en journal_id
            'journal_id' => 'required|exists:journals,id',
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

        // Generar transfer_number automáticamente usando SequenceService
        $sequenceService = app(\App\Services\SequenceService::class);
        $sequence = $sequenceService->getNextNumber($validated['journal_id']);

        DB::transaction(function () use ($validated, $sequence) {
            // Create stock transfer with auto-generated transfer_number
            $transfer = StockTransfer::create([
                'transfer_number' => $sequence['full_number'], // e.g., "TRF-00000001"
                'journal_id' => $validated['journal_id'],
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
                    'price' => 0, // Transfers don't have prices
                    'discount' => 0,
                    'tax_amount' => 0,
                    'subtotal' => 0,
                    'total' => 0,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Register Kardex if status is RECEIVED
            if ($transfer->status === StockTransfer::STATUS_RECEIVED) {
                foreach ($validated['items'] as $item) {
                    // 1. Output from Source
                    Kardex::registerExit(
                        $transfer,
                        [
                            'id' => $item['product_id'],
                            'quantity' => (float) $item['quantity'],
                        ],
                        $transfer->from_warehouse_id,
                        "Transfer Out #{$transfer->transfer_number}"
                    );

                    // 2. Input to Destination
                    // For transfers, we ideally want to carry over the cost.
                    // But with generic registerEntry, it calculates cost based on input price.
                    // We need to fetch the cost from the exit we just did, or current average cost.
                    // Since we don't have easy access to the cost calculated in registerExit without modifying it to return data,
                    // we will fetch the last record from source warehouse to get the cost.
                    // This is a bit inefficient but necessary with this pattern unless we change registerExit return type.
                    
                    // Let's use the Facade/Service to get last record cost
                    $lastSource = Kardex::getLastRecord($item['product_id'], $transfer->from_warehouse_id);
                    $cost = $lastSource['cost'];

                    Kardex::registerEntry(
                        $transfer,
                        [
                            'id' => $item['product_id'],
                            'quantity' => (float) $item['quantity'],
                            'price' => $cost, // Use source cost as input price
                        ],
                        $transfer->to_warehouse_id,
                        "Transfer In #{$transfer->transfer_number}"
                    );
                }
            }
        });

        return redirect()->route('stock-transfers.index')
            ->with('success', 'Stock transfer created successfully.');
    }

    // ... edit method unchanged ...
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
            // journal_id y transfer_number no se pueden editar después de creación
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
            $originalStatus = $transfer->status;

            // Update stock transfer (transfer_number y journal_id no se modifican)
            $transfer->update([
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
                    'price' => 0,
                    'discount' => 0,
                    'tax_amount' => 0,
                    'subtotal' => 0,
                    'total' => 0,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Handle Kardex Logic
            // 1. If status changed TO 'received', register transfer
            if ($originalStatus !== StockTransfer::STATUS_RECEIVED && $transfer->status === StockTransfer::STATUS_RECEIVED) {
                foreach ($validated['items'] as $item) {
                    // Output Source
                    Kardex::registerExit(
                        $transfer,
                        [
                            'id' => $item['product_id'],
                            'quantity' => (float) $item['quantity'],
                        ],
                        $transfer->from_warehouse_id,
                        "Transfer Out #{$transfer->transfer_number}"
                    );

                    // Input Destination
                    $lastSource = Kardex::getLastRecord($item['product_id'], $transfer->from_warehouse_id);
                    $cost = $lastSource['cost'];

                    Kardex::registerEntry(
                        $transfer,
                        [
                            'id' => $item['product_id'],
                            'quantity' => (float) $item['quantity'],
                            'price' => $cost,
                        ],
                        $transfer->to_warehouse_id,
                        "Transfer In #{$transfer->transfer_number}"
                    );
                }
            }
            // 2. If status changed FROM 'received' TO 'cancelled', void movement
            elseif ($originalStatus === StockTransfer::STATUS_RECEIVED && $transfer->status === StockTransfer::STATUS_CANCELLED) {
                foreach ($validated['items'] as $item) {
                    // Reverse Output Source (Create Entry)
                    Kardex::registerEntry(
                        $transfer,
                        [
                            'id' => $item['product_id'],
                            'quantity' => (float) $item['quantity'],
                            'price' => 0, // Cost? Ideally original cost.
                        ],
                        $transfer->from_warehouse_id,
                        "VOID Transfer Out #{$transfer->transfer_number}"
                    );

                    // Reverse Input Destination (Create Exit)
                    Kardex::registerExit(
                        $transfer,
                        [
                            'id' => $item['product_id'],
                            'quantity' => (float) $item['quantity'],
                        ],
                        $transfer->to_warehouse_id,
                        "VOID Transfer In #{$transfer->transfer_number}"
                    );
                }
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
        $transfer = StockTransfer::with('productables')->findOrFail($id);
        
        DB::transaction(function () use ($transfer) {
            // If deleting a received transfer, void movement
            if ($transfer->status === StockTransfer::STATUS_RECEIVED) {
                foreach ($transfer->productables as $item) {
                    // Reverse Output Source
                    Kardex::registerEntry(
                        $transfer,
                        [
                            'id' => $item->product_id,
                            'quantity' => (float) $item->quantity,
                            'price' => 0,
                        ],
                        $transfer->from_warehouse_id,
                        "VOID Transfer Out #{$transfer->transfer_number}"
                    );

                    // Reverse Input Destination
                    Kardex::registerExit(
                        $transfer,
                        [
                            'id' => $item->product_id,
                            'quantity' => (float) $item->quantity,
                        ],
                        $transfer->to_warehouse_id,
                        "VOID Transfer In #{$transfer->transfer_number}"
                    );
                }
            }

            $transfer->productables()->delete();
            $transfer->delete();
        });

        return redirect()->route('stock-transfers.index')
            ->with('success', 'Stock transfer deleted successfully.');
    }
}
