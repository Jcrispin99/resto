<?php

namespace App\Http\Controllers;

use App\Http\Resources\UnitResource;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = Unit::latest()->paginate(10);

        return Inertia::render('Units/Index', [
            'units' => UnitResource::collection($units),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Units/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:units,code',
            'name' => 'required|string|max:100',
            'abbreviation' => 'required|string|max:10',
            'type' => ['required', 'string', Rule::in([Unit::TYPE_WEIGHT, Unit::TYPE_VOLUME, Unit::TYPE_LENGTH, Unit::TYPE_UNIT])],
            'is_active' => 'boolean',
        ]);

        Unit::create($validated);

        return redirect()->route('units.index')
            ->with('success', 'Unit created successfully.');
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
        $unit = Unit::findOrFail($id);

        return Inertia::render('Units/Edit', [
            'unit' => new UnitResource($unit),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $unit = Unit::findOrFail($id);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', Rule::unique('units')->ignore($unit->id)],
            'name' => 'required|string|max:100',
            'abbreviation' => 'required|string|max:10',
            'type' => ['required', 'string', Rule::in([Unit::TYPE_WEIGHT, Unit::TYPE_VOLUME, Unit::TYPE_LENGTH, Unit::TYPE_UNIT])],
            'is_active' => 'boolean',
        ]);

        $unit->update($validated);

        return redirect()->route('units.index')
            ->with('success', 'Unit updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return redirect()->route('units.index')
            ->with('success', 'Unit deleted successfully.');
    }
}
