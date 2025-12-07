<?php

namespace App\Http\Controllers;

use App\Models\TableArea;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TableAreaController extends Controller
{
    /**
     * Display a listing of table areas.
     */
    public function index()
    {
        $areas = TableArea::with('branch')
            ->withCount('tables')
            ->orderBy('order')
            ->get();

        return Inertia::render('TableAreas/Index', [
            'areas' => $areas,
        ]);
    }

    /**
     * Show the form for creating a new area.
     */
    public function create()
    {
        return Inertia::render('TableAreas/Create', [
            'branches' => Company::all(),
        ]);
    }

    /**
     * Store a newly created area.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:companies,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        TableArea::create($validated);

        return redirect()
            ->route('table-areas.index')
            ->with('success', 'Table area created successfully.');
    }

    /**
     * Show the form for editing the area.
     */
    public function edit(TableArea $tableArea)
    {
        return Inertia::render('TableAreas/Edit', [
            'area' => $tableArea->load('branch'),
            'branches' => Company::all(),
        ]);
    }

    /**
     * Update the specified area.
     */
    public function update(Request $request, TableArea $tableArea)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:companies,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $tableArea->update($validated);

        return redirect()
            ->route('table-areas.index')
            ->with('success', 'Table area updated successfully.');
    }

    /**
     * Remove the specified area.
     */
    public function destroy(TableArea $tableArea)
    {
        // Check if area has tables
        if ($tableArea->tables()->count() > 0) {
            return redirect()
                ->route('table-areas.index')
                ->with('error', 'Cannot delete area with existing tables.');
        }

        $tableArea->delete();

        return redirect()
            ->route('table-areas.index')
            ->with('success', 'Table area deleted successfully.');
    }
}
