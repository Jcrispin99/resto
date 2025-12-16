<?php

namespace App\Http\Controllers;

use App\Models\KitchenStation;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KitchenStationController extends Controller
{
    /**
     * Display a listing of kitchen stations.
     */
    public function index()
    {
        $stations = KitchenStation::with('branch')
            ->latest()
            ->paginate(15);

        return Inertia::render('KitchenStations/Index', [
            'stations' => $stations,
        ]);
    }

    /**
     * Show the form for creating a new kitchen station.
     */
    public function create()
    {
        $branches = Company::where('parent_id', '!=', null)
            ->orWhereNull('parent_id')
            ->get();

        return Inertia::render('KitchenStations/Create', [
            'branches' => $branches,
        ]);
    }

    /**
     * Store a newly created kitchen station.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'branch_id' => 'required|exists:companies,id',
            'description' => 'nullable|string|max:500',
            'printer_ip' => 'nullable|ip',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        KitchenStation::create($validated);

        return redirect()
            ->route('kitchen-stations.index')
            ->with('success', 'Estación de cocina creada correctamente.');
    }

    /**
     * Show the form for editing the specified kitchen station.
     */
    public function edit(KitchenStation $kitchenStation)
    {
        $branches = Company::where('parent_id', '!=', null)
            ->orWhereNull('parent_id')
            ->get();

        return Inertia::render('KitchenStations/Edit', [
            'station' => $kitchenStation->load('branch'),
            'branches' => $branches,
        ]);
    }

    /**
     * Update the specified kitchen station.
     */
    public function update(Request $request, KitchenStation $kitchenStation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'branch_id' => 'required|exists:companies,id',
            'description' => 'nullable|string|max:500',
            'printer_ip' => 'nullable|ip',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $kitchenStation->update($validated);

        return redirect()
            ->route('kitchen-stations.index')
            ->with('success', 'Estación de cocina actualizada correctamente.');
    }

    /**
     * Remove the specified kitchen station.
     */
    public function destroy(KitchenStation $kitchenStation)
    {
        // Check if station has products assigned
        if ($kitchenStation->products()->count() > 0) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'No se puede eliminar la estación porque tiene productos asignados.']);
        }

        $kitchenStation->delete();

        return redirect()
            ->route('kitchen-stations.index')
            ->with('success', 'Estación de cocina eliminada correctamente.');
    }
}
