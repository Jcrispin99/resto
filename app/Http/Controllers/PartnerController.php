<?php

namespace App\Http\Controllers;

use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Partner::query();

        // Filter by role if requested
        if ($request->get('filter') === 'customers') {
            $query->customers();
        } elseif ($request->get('filter') === 'suppliers') {
            $query->suppliers();
        }

        $partners = $query->latest()->paginate(10);

        return Inertia::render('Partners/Index', [
            'partners' => PartnerResource::collection($partners),
            'filter' => $request->get('filter'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Partners/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:partners,code',
            'partner_type' => ['required', 'string', Rule::in([Partner::TYPE_INDIVIDUAL, Partner::TYPE_COMPANY])],
            'name' => 'required|string|max:200',
            'trade_name' => 'nullable|string|max:200',
            'tax_id' => 'required|string|max:20|unique:partners,tax_id',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'ubigeo_code' => 'nullable|string|max:6',
            'is_customer' => 'boolean',
            'is_supplier' => 'boolean',
            'payment_terms_days' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Partner::create($validated);

        return redirect()->route('partners.index')
            ->with('success', 'Partner created successfully.');
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
        $partner = Partner::findOrFail($id);

        return Inertia::render('Partners/Edit', [
            'partner' => new PartnerResource($partner),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $partner = Partner::findOrFail($id);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('partners')->ignore($partner->id)],
            'partner_type' => ['required', 'string', Rule::in([Partner::TYPE_INDIVIDUAL, Partner::TYPE_COMPANY])],
            'name' => 'required|string|max:200',
            'trade_name' => 'nullable|string|max:200',
            'tax_id' => ['required', 'string', 'max:20', Rule::unique('partners')->ignore($partner->id)],
            'email' => 'required|email|max:150',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'ubigeo_code' => 'nullable|string|max:6',
            'is_customer' => 'boolean',
            'is_supplier' => 'boolean',
            'payment_terms_days' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $partner->update($validated);

        return redirect()->route('partners.index')
            ->with('success', 'Partner updated successfully.');
    }

    /**
     * Search partners for autocomplete.
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type'); // 'suppliers' or 'customers'

        $partners = Partner::query()
            ->when($query, function ($q) use ($query) {
                $q->where(function ($q2) use ($query) {
                    $q2->where('name', 'like', "%{$query}%")
                        ->orWhere('code', 'like', "%{$query}%")
                        ->orWhere('tax_id', 'like', "%{$query}%");
                });
            })
            ->when($type === 'suppliers', fn($q) => $q->suppliers())
            ->when($type === 'customers', fn($q) => $q->customers())
            ->active()
            ->limit(20)
            ->get()
            ->map(function ($partner) {
                return [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'code' => $partner->code,
                    'tax_id' => $partner->tax_id,
                ];
            });

        return response()->json($partners);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $partner = Partner::findOrFail($id);
        $partner->delete();

        return redirect()->route('partners.index')
            ->with('success', 'Partner deleted successfully.');
    }
}
