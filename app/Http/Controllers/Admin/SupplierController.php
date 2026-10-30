<?php

/**
 * ============================================================================
 * FILE PATH: app/Http/Controllers/Admin/SupplierController.php
 * DESCRIPTION: Full CRUD Backend Controller for Module 6 — Suppliers Directory.
 *              Supports:
 *              - READ: Search, Category Filter, Status Filter, Summary KPIs
 *              - CREATE: Add new supplier company
 *              - UPDATE: Edit supplier details, payment terms & balance
 *              - DELETE: Remove supplier record
 *              - ROLE PROTECTION: Admin, Store Manager, Stock Manager (✓); Cashier (❌ 403)
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Routes File: routes/web.php (Routes: 'admin.suppliers.*')
 * - Supplier Model (Database): app/Models/Supplier.php
 * - User Model (Permissions): app/Models/User.php
 * - Suppliers Directory UI: resources/views/admin/suppliers/index.blade.php
 * ============================================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierController extends Controller
{
    /**
     * PART 1: Display the Suppliers Directory Page UI with Filters & KPIs (READ).
     *
     * Route: GET /admin/suppliers (route name: 'admin.suppliers.index')
     * UI View Path: resources/views/admin/suppliers/index.blade.php
     */
    public function index(Request $request): View
    {
        if (! $request->user()->canAccessSuppliers()) {
            abort(403, 'Unauthorized: Cashier role cannot access Suppliers Directory.');
        }

        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', ''));
        $statusFilter = trim((string) $request->query('status', ''));

        $query = Supplier::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('supplier_code', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($category !== '') {
            $query->where('category', $category);
        }

        if ($statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        $suppliers = $query->orderByDesc('id')->get();

        // Calculate Summary KPIs across all suppliers
        $allSuppliers = Supplier::all();
        $summary = [
            'total_suppliers' => $allSuppliers->count(),
            'active_suppliers' => $allSuppliers->where('status', 'active')->count(),
            'inactive_suppliers' => $allSuppliers->where('status', 'inactive')->count(),
            'total_payables' => $allSuppliers->sum(fn (Supplier $s) => (float) $s->outstanding_balance),
        ];

        $categories = \App\Models\Category::where('status', 'active')
            ->pluck('name')
            ->merge($allSuppliers->pluck('category'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('admin.suppliers.index', [
            'suppliers' => $suppliers,
            'summary' => $summary,
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'category' => $category,
                'status' => $statusFilter,
            ],
        ]);
    }

    /**
     * PART 2: Store a Newly Created Supplier in the Database (CREATE).
     *
     * Route: POST /admin/suppliers (route name: 'admin.suppliers.store')
     * Form Source: Add Supplier Modal in resources/views/admin/suppliers/index.blade.php
     */
    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()->canAccessSuppliers()) {
            abort(403, 'Unauthorized: Cashier role cannot create Suppliers.');
        }

        $validated = $request->validate([
            'supplier_code' => ['required', 'string', 'max:40', 'unique:suppliers,supplier_code'],
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:40'],
            'category' => ['required', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'tax_number' => ['nullable', 'string', 'max:60'],
            'payment_terms' => ['required', 'string', 'max:60'],
            'outstanding_balance' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $supplier = Supplier::create($validated);

        // If created from the Add/Edit Product page inline modal, redirect back and pre-select the new supplier
        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))
                ->with('success', "Supplier '{$supplier->company_name}' ({$supplier->supplier_code}) added to the Supplier dropdown!")
                ->with('selected_supplier', $supplier->company_name);
        }

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', "Supplier '{$supplier->company_name}' ({$supplier->supplier_code}) added successfully.");
    }

    /**
     * PART 3: Update an Existing Supplier in the Database (UPDATE).
     *
     * Route: PUT /admin/suppliers/{supplier} (route name: 'admin.suppliers.update')
     * Form Source: Edit Supplier Modal in resources/views/admin/suppliers/index.blade.php
     */
    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        if (! $request->user()->canAccessSuppliers()) {
            abort(403, 'Unauthorized: Cashier role cannot edit Suppliers.');
        }

        $validated = $request->validate([
            'supplier_code' => ['required', 'string', 'max:40', Rule::unique('suppliers', 'supplier_code')->ignore($supplier->id)],
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:40'],
            'category' => ['required', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'tax_number' => ['nullable', 'string', 'max:60'],
            'payment_terms' => ['required', 'string', 'max:60'],
            'outstanding_balance' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $supplier->update($validated);

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', "Supplier '{$supplier->company_name}' ({$supplier->supplier_code}) updated successfully.");
    }

    /**
     * PART 4: Delete a Supplier from the Database (DELETE).
     *
     * Route: DELETE /admin/suppliers/{supplier} (route name: 'admin.suppliers.destroy')
     */
    public function destroy(Request $request, Supplier $supplier): RedirectResponse
    {
        if (! $request->user()->canAccessSuppliers()) {
            abort(403, 'Unauthorized: Cashier role cannot delete Suppliers.');
        }

        $name = $supplier->company_name;
        $code = $supplier->supplier_code;
        $supplier->delete();

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', "Supplier '{$name}' ({$code}) removed from directory.");
    }
}
