<?php

namespace App\Http\Controllers;

use App\Http\Resources\WarehouseResource;
use App\Models\Company;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouses = Warehouse::with('branch')->latest()->paginate(10);

        return Inertia::render('Warehouses/Index', [
            'warehouses' => WarehouseResource::collection($warehouses),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all active branches (companies with parent_id)
        $branches = Company::branches()->active()->get();
        
        return Inertia::render('Warehouses/Create', [
            'branches' => \App\Http\Resources\CompanyResource::collection($branches),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:companies,id',
            'code' => 'required|string|max:20|unique:warehouses,code',
            'name' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        Warehouse::create($validated);

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Not needed for this CRUD
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $warehouse = Warehouse::with('branch')->findOrFail($id);
        $branches = Company::branches()->active()->get();

        return Inertia::render('Warehouses/Edit', [
            'warehouse' => new WarehouseResource($warehouse),
            'branches' => \App\Http\Resources\CompanyResource::collection($branches),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $validated = $request->validate([
            'branch_id' => 'required|exists:companies,id',
            'code' => ['required', 'string', 'max:20', Rule::unique('warehouses')->ignore($warehouse->id)],
            'name' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        $warehouse->update($validated);

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->delete();

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse deleted successfully.');
    }
}
