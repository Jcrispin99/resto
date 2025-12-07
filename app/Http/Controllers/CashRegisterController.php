<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\CashMovement;
use App\Models\PosTerminal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CashRegisterController extends Controller
{
    /**
     * Display a listing of cash registers.
     */
    public function index(Request $request)
    {
        $query = CashRegister::with(['terminal', 'openedBy', 'closedBy']);

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'open') {
                $query->open();
            } elseif ($request->status === 'closed') {
                $query->closed();
            }
        }

        // Filter by terminal
        if ($request->has('terminal_id')) {
            $query->where('terminal_id', $request->terminal_id);
        }

        $cashRegisters = $query->latest('opened_at')->paginate(15);

        return Inertia::render('CashRegisters/Index', [
            'cashRegisters' => $cashRegisters,
            'terminals' => PosTerminal::active()->get(),
            'filters' => $request->only(['status', 'terminal_id']),
        ]);
    }

    /**
     * Show the form for opening a new cash register.
     */
    public function create()
    {
        return Inertia::render('CashRegisters/Create', [
            'terminals' => PosTerminal::active()->get(),
        ]);
    }

    /**
     * Store a newly opened cash register.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'terminal_id' => 'required|exists:pos_terminals,id',
            'opening_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if terminal already has an open register
        $openRegister = CashRegister::where('terminal_id', $validated['terminal_id'])
            ->open()
            ->first();

        if ($openRegister) {
            return redirect()
                ->back()
                ->withErrors(['terminal_id' => 'This terminal already has an open cash register.']);
        }

        DB::beginTransaction();
        try {
            // Create cash register
            $cashRegister = CashRegister::create([
                'terminal_id' => $validated['terminal_id'],
                'opening_balance' => $validated['opening_balance'],
                'opened_by' => Auth::id(),
                'opened_at' => now(),
                'status' => CashRegister::STATUS_OPEN,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Register opening movement
            $cashRegister->movements()->create([
                'type' => CashMovement::TYPE_OPENING,
                'concept' => 'Apertura de caja',
                'amount' => $validated['opening_balance'],
                'payment_method' => 'cash',
                'user_id' => Auth::id(),
            ]);

            DB::commit();

            return redirect()
                ->route('cash-registers.show', $cashRegister)
                ->with('success', 'Cash register opened successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withErrors(['error' => 'Error opening cash register: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified cash register.
     */
    public function show(CashRegister $cashRegister)
    {
        $cashRegister->load(['terminal', 'openedBy', 'closedBy', 'movements.user']);

        return Inertia::render('CashRegisters/Show', [
            'cashRegister' => $cashRegister,
        ]);
    }

    /**
     * Close an open cash register.
     */
    public function close(Request $request, CashRegister $cashRegister)
    {
        if ($cashRegister->status === CashRegister::STATUS_CLOSED) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Cash register is already closed.']);
        }

        $validated = $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // Calculate expected balance
            $totalMovements = $cashRegister->movements()
                ->whereIn('type', [
                    CashMovement::TYPE_OPENING,
                    CashMovement::TYPE_INCOME,
                    CashMovement::TYPE_EXPENSE,
                    CashMovement::TYPE_DEPOSIT,
                    CashMovement::TYPE_WITHDRAWAL,
                ])
                ->sum('amount');

            $expectedBalance = $totalMovements;
            $difference = $validated['closing_balance'] - $expectedBalance;

            // Update cash register
            $cashRegister->update([
                'closing_balance' => $validated['closing_balance'],
                'expected_balance' => $expectedBalance,
                'difference' => $difference,
                'closed_by' => Auth::id(),
                'closed_at' => now(),
                'status' => CashRegister::STATUS_CLOSED,
                'notes' => ($cashRegister->notes ?? '') . "\n\n" . ($validated['notes'] ?? ''),
            ]);

            // Register closing movement
            $cashRegister->movements()->create([
                'type' => CashMovement::TYPE_CLOSING,
                'concept' => 'Cierre de caja',
                'amount' => $validated['closing_balance'],
                'payment_method' => 'cash',
                'user_id' => Auth::id(),
                'notes' => $difference != 0 
                    ? "Diferencia: S/ " . number_format(abs($difference), 2) . " (" . ($difference > 0 ? 'Sobrante' : 'Faltante') . ")"
                    : null,
            ]);

            DB::commit();

            return redirect()
                ->route('cash-registers.show', $cashRegister)
                ->with('success', 'Cash register closed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withErrors(['error' => 'Error closing cash register: ' . $e->getMessage()]);
        }
    }

    /**
     * Add a cash movement to the register.
     */
    public function addMovement(Request $request, CashRegister $cashRegister)
    {
        if ($cashRegister->status === CashRegister::STATUS_CLOSED) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Cannot add movements to a closed cash register.']);
        }

        $validated = $request->validate([
            'type' => 'required|in:income,expense,deposit,withdrawal',
            'concept' => 'required|string|max:200',
            'amount' => 'required|numeric',
            'payment_method' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        // Ensure correct sign for amount based on type
        $amount = abs($validated['amount']);
        if (in_array($validated['type'], [CashMovement::TYPE_EXPENSE, CashMovement::TYPE_DEPOSIT, CashMovement::TYPE_WITHDRAWAL])) {
            $amount = -$amount;
        }

        $cashRegister->movements()->create([
            'type' => $validated['type'],
            'concept' => $validated['concept'],
            'amount' => $amount,
            'payment_method' => $validated['payment_method'] ?? 'cash',
            'reference' => $validated['reference'] ?? null,
            'user_id' => Auth::id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('cash-registers.show', $cashRegister)
            ->with('success', 'Movement added successfully.');
    }
}
