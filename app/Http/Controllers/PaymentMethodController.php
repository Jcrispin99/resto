<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of payment methods.
     */
    public function index()
    {
        $paymentMethods = PaymentMethod::latest()->get();

        return Inertia::render('PaymentMethods/Index', [
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Show the form for creating a new payment method.
     */
    public function create()
    {
        return Inertia::render('PaymentMethods/Create');
    }

    /**
     * Store a newly created payment method.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:payment_methods,code',
            'name' => 'required|string|max:50',
            'type' => 'required|in:cash,card,transfer,wallet,other',
            'requires_reference' => 'boolean',
            'is_active' => 'boolean',
        ]);

        PaymentMethod::create($validated);

        return redirect()
            ->route('payment-methods.index')
            ->with('success', 'Payment method created successfully.');
    }

    /**
     * Show the form for editing the payment method.
     */
    public function edit(PaymentMethod $paymentMethod)
    {
        return Inertia::render('PaymentMethods/Edit', [
            'paymentMethod' => $paymentMethod,
        ]);
    }

    /**
     * Update the specified payment method.
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:payment_methods,code,' . $paymentMethod->id,
            'name' => 'required|string|max:50',
            'type' => 'required|in:cash,card,transfer,wallet,other',
            'requires_reference' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $paymentMethod->update($validated);

        return redirect()
            ->route('payment-methods.index')
            ->with('success', 'Payment method updated successfully.');
    }

    /**
     * Remove the specified payment method.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();

        return redirect()
            ->route('payment-methods.index')
            ->with('success', 'Payment method deleted successfully.');
    }
}
