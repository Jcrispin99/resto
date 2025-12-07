<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Table;
use App\Models\Partner;
use App\Models\Company;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReservationController extends Controller
{
    /**
     * Display a listing of reservations.
     */
    public function index(Request $request)
    {
        $query = Reservation::with(['branch', 'partner', 'table']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('reservation_date', $request->date);
        } else {
            // Default to upcoming reservations
            $query->upcoming();
        }

        $reservations = $query->orderBy('reservation_date')
            ->orderBy('reservation_time')
            ->paginate(15);

        return Inertia::render('Reservations/Index', [
            'reservations' => $reservations,
            'filters' => $request->only(['status', 'date']),
        ]);
    }

    /**
     * Show the form for creating a new reservation.
     */
    public function create()
    {
        return Inertia::render('Reservations/Create', [
            'branches' => Company::all(),
            'tables' => Table::with('area')->where('status', '!=', 'occupied')->get(),
        ]);
    }

    /**
     * Store a newly created reservation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:companies,id',
            'partner_id' => 'required|exists:partners,id',
            'table_id' => 'nullable|exists:tables,id',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required|date_format:H:i',
            'guests_count' => 'required|integer|min:1',
            'special_requests' => 'nullable|string',
        ]);

        // Generate reservation number
        $validated['reservation_number'] = 'RES-' . str_pad(Reservation::count() + 1, 6, '0', STR_PAD_LEFT);
        $validated['status'] = Reservation::STATUS_PENDING;

        $reservation = Reservation::create($validated);

        // If table is assigned, update table status
        if ($validated['table_id']) {
            Table::find($validated['table_id'])->update(['status' => Table::STATUS_RESERVED]);
        }

        return redirect()
            ->route('reservations.show', $reservation)
            ->with('success', 'Reservation created successfully.');
    }

    /**
     * Display the specified reservation.
     */
    public function show(Reservation $reservation)
    {
        $reservation->load(['branch', 'partner', 'table.area']);

        return Inertia::render('Reservations/Show', [
            'reservation' => $reservation,
        ]);
    }

    /**
     * Show the form for editing the reservation.
     */
    public function edit(Reservation $reservation)
    {
        return Inertia::render('Reservations/Edit', [
            'reservation' => $reservation->load(['branch', 'partner', 'table']),
            'branches' => Company::all(),
            'tables' => Table::with('area')->where('status', '!=', 'occupied')->get(),
        ]);
    }

    /**
     * Update the specified reservation.
     */
    public function update(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:companies,id',
            'partner_id' => 'required|exists:partners,id',
            'table_id' => 'nullable|exists:tables,id',
            'reservation_date' => 'required|date',
            'reservation_time' => 'required|date_format:H:i',
            'guests_count' => 'required|integer|min:1',
            'special_requests' => 'nullable|string',
        ]);

        // Handle table change
        if ($reservation->table_id && $reservation->table_id != $validated['table_id']) {
            // Free old table
            Table::find($reservation->table_id)->update(['status' => Table::STATUS_AVAILABLE]);
        }

        // Reserve new table
        if ($validated['table_id']) {
            Table::find($validated['table_id'])->update(['status' => Table::STATUS_RESERVED]);
        }

        $reservation->update($validated);

        return redirect()
            ->route('reservations.show', $reservation)
            ->with('success', 'Reservation updated successfully.');
    }

    /**
     * Confirm a reservation.
     */
    public function confirm(Reservation $reservation)
    {
        $reservation->update([
            'status' => Reservation::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Reservation confirmed.');
    }

    /**
     * Seat customers (mark as seated and create order).
     */
    public function seat(Request $request, Reservation $reservation)
    {
        DB::beginTransaction();
        try {
            // Update reservation
            $reservation->update([
                'status' => Reservation::STATUS_SEATED,
                'seated_at' => now(),
            ]);

            // Update table status
            if ($reservation->table_id) {
                Table::find($reservation->table_id)->update(['status' => Table::STATUS_OCCUPIED]);
            }

            // Create order
            $order = Order::create([
                'order_number' => 'ORD-' . str_pad(Order::count() + 1, 6, '0', STR_PAD_LEFT),
                'branch_id' => $reservation->branch_id,
                'table_id' => $reservation->table_id,
                'partner_id' => $reservation->partner_id,
                'order_type' => Order::TYPE_DINE_IN,
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_UNPAID,
                'guests_count' => $reservation->guests_count,
                'order_date' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('orders.show', $order)
                ->with('success', 'Customers seated and order created.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Error seating customers: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a reservation.
     */
    public function cancel(Reservation $reservation)
    {
        // Free table if reserved
        if ($reservation->table_id) {
            Table::find($reservation->table_id)->update(['status' => Table::STATUS_AVAILABLE]);
        }

        $reservation->update([
            'status' => Reservation::STATUS_CANCELLED,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Reservation cancelled.');
    }

    /**
     * Mark as no show.
     */
    public function noShow(Reservation $reservation)
    {
        // Free table if reserved
        if ($reservation->table_id) {
            Table::find($reservation->table_id)->update(['status' => Table::STATUS_AVAILABLE]);
        }

        $reservation->update([
            'status' => Reservation::STATUS_NO_SHOW,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Reservation marked as no show.');
    }

    /**
     * Remove the specified reservation.
     */
    public function destroy(Reservation $reservation)
    {
        // Can only delete pending or cancelled reservations
        if (!in_array($reservation->status, [Reservation::STATUS_PENDING, Reservation::STATUS_CANCELLED, Reservation::STATUS_NO_SHOW])) {
            return redirect()
                ->back()
                ->with('error', 'Can only delete pending, cancelled, or no-show reservations.');
        }

        // Free table if reserved
        if ($reservation->table_id && $reservation->status != Reservation::STATUS_CANCELLED) {
            Table::find($reservation->table_id)->update(['status' => Table::STATUS_AVAILABLE]);
        }

        $reservation->delete();

        return redirect()
            ->route('reservations.index')
            ->with('success', 'Reservation deleted successfully.');
    }
}
