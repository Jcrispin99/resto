<?php

namespace App\Http\Controllers;

use App\Models\Combo;
use App\Models\ProductTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ComboController extends Controller
{
    /**
     * Display a listing of combos.
     */
    public function index(Request $request)
    {
        $query = Combo::with('items.product');

        // Filter by active status
        if ($request->has('active')) {
            if ($request->active === '1') {
                $query->active();
            } elseif ($request->active === '0') {
                $query->where('is_active', false);
            }
        }

        $combos = $query->latest()->paginate(15);

        return Inertia::render('Combos/Index', [
            'combos' => $combos,
            'filters' => $request->only(['active']),
        ]);
    }

    /**
     * Show the form for creating a new combo.
     */
    public function create()
    {
        return Inertia::render('Combos/Create', [
            'products' => ProductTemplate::where('can_be_sold', true)->get(),
        ]);
    }

    /**
     * Store a newly created combo in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'regular_price' => 'required|numeric|min:0',
            'image' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.product_template_id' => 'required|exists:product_template,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.allow_substitution' => 'boolean',
        ]);

        DB::transaction(function () use ($validated) {
            // Calculate discount percentage
            $discountPercentage = 0;
            if ($validated['regular_price'] > 0) {
                $discountPercentage = (($validated['regular_price'] - $validated['price']) / $validated['regular_price']) * 100;
            }

            $combo = Combo::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'regular_price' => $validated['regular_price'],
                'discount_percentage' => round($discountPercentage, 2),
                'image' => $validated['image'] ?? null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Create combo items
            foreach ($validated['items'] as $item) {
                $combo->items()->create([
                    'product_template_id' => $item['product_template_id'],
                    'quantity' => $item['quantity'],
                    'allow_substitution' => $item['allow_substitution'] ?? false,
                ]);
            }
        });

        return redirect()
            ->route('combos.index')
            ->with('success', 'Combo created successfully.');
    }

    /**
     * Display the specified combo.
     */
    public function show(Combo $combo)
    {
        return Inertia::render('Combos/Show', [
            'combo' => $combo->load('items.product'),
        ]);
    }

    /**
     * Show the form for editing the specified combo.
     */
    public function edit(Combo $combo)
    {
        return Inertia::render('Combos/Edit', [
            'combo' => $combo->load('items.product'),
            'products' => ProductTemplate::where('can_be_sold', true)->get(),
        ]);
    }

    /**
     * Update the specified combo in storage.
     */
    public function update(Request $request, Combo $combo)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'regular_price' => 'required|numeric|min:0',
            'image' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:combo_items,id',
            'items.*.product_template_id' => 'required|exists:product_template,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.allow_substitution' => 'boolean',
        ]);

        DB::transaction(function () use ($validated, $combo) {
            // Calculate discount percentage
            $discountPercentage = 0;
            if ($validated['regular_price'] > 0) {
                $discountPercentage = (($validated['regular_price'] - $validated['price']) / $validated['regular_price']) * 100;
            }

            $combo->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'regular_price' => $validated['regular_price'],
                'discount_percentage' => round($discountPercentage, 2),
                'image' => $validated['image'] ?? null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Delete removed items
            $itemIds = collect($validated['items'])->pluck('id')->filter();
            $combo->items()->whereNotIn('id', $itemIds)->delete();

            // Update or create items
            foreach ($validated['items'] as $item) {
                if (isset($item['id'])) {
                    $combo->items()->where('id', $item['id'])->update([
                        'product_template_id' => $item['product_template_id'],
                        'quantity' => $item['quantity'],
                        'allow_substitution' => $item['allow_substitution'] ?? false,
                    ]);
                } else {
                    $combo->items()->create([
                        'product_template_id' => $item['product_template_id'],
                        'quantity' => $item['quantity'],
                        'allow_substitution' => $item['allow_substitution'] ?? false,
                    ]);
                }
            }
        });

        return redirect()
            ->route('combos.index')
            ->with('success', 'Combo updated successfully.');
    }

    /**
     * Remove the specified combo from storage.
     */
    public function destroy(Combo $combo)
    {
        $combo->delete();

        return redirect()
            ->route('combos.index')
            ->with('success', 'Combo deleted successfully.');
    }
}
