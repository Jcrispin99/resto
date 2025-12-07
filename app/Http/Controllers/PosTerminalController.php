<?php

namespace App\Http\Controllers;

use App\Models\PosTerminal;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PosTerminalController extends Controller
{
    /**
     * Display a listing of POS terminals.
     */
    public function index()
    {
        $terminals = PosTerminal::with('branch')
            ->latest()
            ->get();

        return Inertia::render('PosTerminals/Index', [
            'terminals' => $terminals,
        ]);
    }

    /**
     * Show the form for creating a new terminal.
     */
    public function create()
    {
        return Inertia::render('PosTerminals/Create', [
            'branches' => Company::all(),
        ]);
    }

    /**
     * Store a newly created terminal.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:companies,id',
            'code' => 'required|string|max:20|unique:pos_terminals,code',
            'name' => 'required|string|max:100',
            'ip_address' => 'nullable|ip',
            'printer_ip' => 'nullable|ip',
            'is_active' => 'boolean',
        ]);

        PosTerminal::create($validated);

        return redirect()
            ->route('pos-terminals.index')
            ->with('success', 'POS Terminal created successfully.');
    }

    /**
     * Show the form for editing the terminal.
     */
    public function edit(PosTerminal $posTerminal)
    {
        return Inertia::render('PosTerminals/Edit', [
            'terminal' => $posTerminal->load('branch'),
            'branches' => Company::all(),
        ]);
    }

    /**
     * Update the specified terminal.
     */
    public function update(Request $request, PosTerminal $posTerminal)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:companies,id',
            'code' => 'required|string|max:20|unique:pos_terminals,code,' . $posTerminal->id,
            'name' => 'required|string|max:100',
            'ip_address' => 'nullable|ip',
            'printer_ip' => 'nullable|ip',
            'is_active' => 'boolean',
        ]);

        $posTerminal->update($validated);

        return redirect()
            ->route('pos-terminals.index')
            ->with('success', 'POS Terminal updated successfully.');
    }

    /**
     * Remove the specified terminal.
     */
    public function destroy(PosTerminal $posTerminal)
    {
        // Check if terminal has open cash registers
        if ($posTerminal->cashRegisters()->open()->exists()) {
            return redirect()
                ->route('pos-terminals.index')
                ->with('error', 'Cannot delete terminal with open cash registers.');
        }

        $posTerminal->delete();

        return redirect()
            ->route('pos-terminals.index')
            ->with('success', 'POS Terminal deleted successfully.');
    }
}
