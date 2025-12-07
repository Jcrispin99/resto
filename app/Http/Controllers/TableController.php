<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\TableArea;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TableController extends Controller
{
    /**
     * Display a listing of tables.
     */
    public function index(Request $request)
    {
        $query = Table::with(['branch', 'area']);

        // Filter by area
        if ($request->has('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        // Filter by is_active
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $tables = $query->orderBy('number')->get();

        return Inertia::render('Tables/Index', [
            'tables' => $tables,
            'areas' => TableArea::active()->get(),
            'filters' => $request->only(['area_id', 'is_active']),
        ]);
    }

    /**
     * Show the form for creating a new table.
     */
    public function create()
    {
        return Inertia::render('Tables/Create', [
            'branches' => Company::all(),
            'areas' => TableArea::active()->get(),
        ]);
    }

    /**
     * Store a newly created table.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:companies,id',
            'area_id' => 'required|exists:table_areas,id',
            'number' => 'required|string|max:20',
            'capacity' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        Table::create($validated);

        return redirect()
            ->route('tables.index')
            ->with('success', 'Table created successfully.');
    }

    /**
     * Show the form for editing the table.
     */
    public function edit(Table $table)
    {
        return Inertia::render('Tables/Edit', [
            'table' => $table->load(['branch', 'area']),
            'branches' => Company::all(),
            'areas' => TableArea::active()->get(),
        ]);
    }

    /**
     * Update the specified table.
     */
    public function update(Request $request, Table $table)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:companies,id',
            'area_id' => 'required|exists:table_areas,id',
            'number' => 'required|string|max:20',
            'capacity' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $table->update($validated);

        return redirect()
            ->route('tables.index')
            ->with('success', 'Table updated successfully.');
    }

    /**
     * Update table active status.
     */
    public function updateStatus(Request $request, Table $table)
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $table->update($validated);

        return redirect()
            ->back()
            ->with('success', 'Table status updated successfully.');
    }

    /**
     * Remove the specified table.
     */
    public function destroy(Table $table)
    {
        // Check if table has active orders
        if ($table->orders()->whereIn('status', ['pending', 'confirmed', 'preparing'])->exists()) {
            return redirect()
                ->route('tables.index')
                ->with('error', 'Cannot delete table with active orders.');
        }

        $table->delete();

        return redirect()
            ->route('tables.index')
            ->with('success', 'Table deleted successfully.');
    }
}
