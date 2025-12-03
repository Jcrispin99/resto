<?php

namespace App\Http\Controllers;

use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Only show matrices (parent companies) in main list
        $companies = Company::matrices()->withBranchCount()->latest()->paginate(10);

        return Inertia::render('Companies/Index', [
            'companies' => CompanyResource::collection($companies),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all matrices for parent selection (if creating a branch)
        $matrices = Company::matrices()->active()->get();
        
        return Inertia::render('Companies/Create', [
            'matrices' => CompanyResource::collection($matrices),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $isBranch = $request->filled('parent_id');

        $rules = [
            'parent_id' => 'nullable|exists:companies,id',
            'name' => 'required|string|max:200',
            'business_name' => 'required|string|max:200',
            'trade_name' => 'required|string|max:200',
            'tax_id' => 'required|string|max:20',
            'logo' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            
            // Conditional validation based on branch/matriz
            'code' => $isBranch ? 'required|string|max:10|unique:companies,code' : 'nullable',
            'address' => $isBranch ? 'required|string|max:255' : 'nullable',
            'phone' => $isBranch ? 'required|string|max:20' : 'nullable|string|max:20',
            'email' => $isBranch ? 'required|email|max:150' : 'nullable|email|max:150',
            
            // Always optional
            'website' => 'nullable|url|max:255',
            'ubigeo_code' => 'nullable|string|max:6',
            'opening_time' => 'nullable|date_format:H:i:s',
            'closing_time' => 'nullable|date_format:H:i:s',
        ];

        $validated = $request->validate($rules);

        Company::create($validated);

        return redirect()->route('companies.index')
            ->with('success', $isBranch ? 'Branch created successfully.' : 'Company created successfully.');
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
        $company = Company::findOrFail($id);
        $matrices = Company::matrices()->active()->where('id', '!=', $id)->get();

        return Inertia::render('Companies/Edit', [
            'company' => new CompanyResource($company),
            'matrices' => CompanyResource::collection($matrices),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $company = Company::findOrFail($id);
        $isBranch = $request->filled('parent_id');

        $rules = [
            'parent_id' => 'nullable|exists:companies,id',
            'name' => 'required|string|max:200',
            'business_name' => 'required|string|max:200',
            'trade_name' => 'required|string|max:200',
            'tax_id' => 'required|string|max:20',
            'logo' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            
            // Conditional validation
            'code' => $isBranch ? ['required', 'string', 'max:10', Rule::unique('companies')->ignore($company->id)] : 'nullable',
            'address' => $isBranch ? 'required|string|max:255' : 'nullable',
            'phone' => $isBranch ? 'required|string|max:20' : 'nullable|string|max:20',
            'email' => $isBranch ? 'required|email|max:150' : 'nullable|email|max:150',
            
            // Always optional
            'website' => 'nullable|url|max:255',
            'ubigeo_code' => 'nullable|string|max:6',
            'opening_time' => 'nullable|date_format:H:i:s',
            'closing_time' => 'nullable|date_format:H:i:s',
        ];

        $validated = $request->validate($rules);
        $company->update($validated);

        return redirect()->route('companies.index')
            ->with('success', $isBranch ? 'Branch updated successfully.' : 'Company updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $company = Company::findOrFail($id);

        // Children will be deleted automatically due to cascade
        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', 'Company deleted successfully.');
    }
}
