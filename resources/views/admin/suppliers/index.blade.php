{{--
================================================================================
FILE PATH: resources/views/admin/suppliers/index.blade.php
DESCRIPTION: Suppliers Directory Page UI with Full CRUD (Module 6 of ISM System).
             Extends the shared Master Layout (resources/views/layouts/admin.blade.php)
             so the dynamic Sidebar, Top Navbar, Footer, CSS, and JS are loaded
             automatically via @section() blocks.
================================================================================

SHARED LAYOUT & PARTIALS USED:
- Master Layout: resources/views/layouts/admin.blade.php
- Sidebar Partial: resources/views/partials/sidebar.blade.php
- Navbar Partial: resources/views/partials/navbar.blade.php
- Footer Partial: resources/views/partials/footer.blade.php
- Shared CSS / JS: public/assets/css/ism-admin.css & public/assets/js/ism-admin.js

CONNECTED BACKEND & ROUTE FILES:
- Route (GET /admin/suppliers): routes/web.php -> SupplierController@index ('admin.suppliers.index')
- Route (POST /admin/suppliers): routes/web.php -> SupplierController@store ('admin.suppliers.store')
- Route (PUT /admin/suppliers/{supplier}): routes/web.php -> SupplierController@update ('admin.suppliers.update')
- Route (DELETE /admin/suppliers/{supplier}): routes/web.php -> SupplierController@destroy ('admin.suppliers.destroy')
- Backend Controller: app/Http/Controllers/Admin/SupplierController.php
- Supplier Model: app/Models/Supplier.php
================================================================================
--}}
@extends('layouts.admin')

{{-- =======================================================================
     PART 1: Dynamic Navbar Header Sections (Injected into partials/navbar.blade.php)
     ======================================================================= --}}
@section('title', 'Suppliers Directory | ISM System')
@section('page_title', 'Suppliers Directory')
@section('page_badge')
<span class="badge text-bg-primary bg-opacity-10 text-primary border border-primary-subtle">
    Vendor &amp; Supply Chain Management
</span>
@endsection
@section('page_subtitle', 'Manage vendor companies, contact persons, payment terms, and payable balances')

@section('topbar_actions')
<button type="button" class="btn btn-primary btn-sm fw-semibold d-inline-flex align-items-center gap-1 px-3 py-2 rounded-3"
    data-bs-toggle="modal" data-bs-target="#addSupplierModal">
    <i class="bi bi-plus-lg"></i> Add New Supplier
</button>
@endsection

{{-- =======================================================================
     PART 2: Main Suppliers Directory Content (Injected into layouts/admin.blade.php)
     ======================================================================= --}}
@section('content')

{{-- SECTION A: Supplier Summary KPI Cards ($summary from SupplierController@index) --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
            <div>
                <div class="text-secondary small fw-medium">Total Suppliers</div>
                <h4 class="fw-bold mb-0 text-dark">{{ number_format($summary['total_suppliers'] ?? 0) }}</h4>
                <small class="text-muted">Registered vendors</small>
            </div>
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-truck"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
            <div>
                <div class="text-secondary small fw-medium">Active Suppliers</div>
                <h4 class="fw-bold mb-0 text-success">{{ number_format($summary['active_suppliers'] ?? 0) }}</h4>
                <small class="text-muted">Ready for Purchase Orders</small>
            </div>
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-building-check"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
            <div>
                <div class="text-secondary small fw-medium">Total Payable Balance</div>
                <h5 class="fw-bold mb-0 text-danger">LKR {{ number_format($summary['total_payables'] ?? 0, 2) }}</h5>
                <small class="text-muted">Outstanding credit dues</small>
            </div>
            <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                <i class="bi bi-wallet2"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
            <div>
                <div class="text-secondary small fw-medium">Inactive Vendors</div>
                <h4 class="fw-bold mb-0 text-secondary">{{ number_format($summary['inactive_suppliers'] ?? 0) }}</h4>
                <small class="text-muted">On hold / archived</small>
            </div>
            <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                <i class="bi bi-building-slash"></i>
            </div>
        </div>
    </div>
</div>

{{-- SECTION B: Search & Filter Bar --}}
<div class="content-card p-3 mb-4">
    <form action="{{ route('admin.suppliers.index') }}" method="GET" class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label small fw-semibold text-secondary mb-1">Search Company / Code / Contact / Phone</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    class="form-control"
                    placeholder="Search by company name, SUP code, contact person, phone, or city...">
            </div>
        </div>

        <div class="col-6 col-md-3">
            <label class="form-label small fw-semibold text-secondary mb-1">Supply Category</label>
            <select name="category" class="form-select form-select-sm">
                <option value="">All Categories</option>
                @foreach ($categories as $cat)
                <option value="{{ $cat }}" {{ ($filters['category'] ?? '') === $cat ? 'selected' : '' }}>
                    {{ $cat }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="col-6 col-md-2">
            <label class="form-label small fw-semibold text-secondary mb-1">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>
</div>

{{-- SECTION C: Suppliers Directory Table --}}
<div class="content-card p-3 p-sm-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div>
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-building text-primary me-1"></i> Registered Suppliers ({{ $suppliers->count() }} companies)
            </h6>
            <small class="text-muted">Click View, Edit, or Delete to manage vendor profiles</small>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>Supplier / Code</th>
                    <th>Contact Person</th>
                    <th>Phone &amp; Email</th>
                    <th>Category / City</th>
                    <th>Payment Terms</th>
                    <th class="text-end">Payable (LKR)</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $supplier)
                <tr>
                    <!-- Company Name & Code -->
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="product-icon-box">
                                <i class="bi bi-truck"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark">{{ $supplier->company_name }}</div>
                                <div>
                                    <code class="text-primary">{{ $supplier->supplier_code }}</code>
                                    @if ($supplier->tax_number)
                                    <small class="text-muted ms-1">&bull; Tax: {{ $supplier->tax_number }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- Contact Person -->
                    <td class="fw-medium text-dark">
                        {{ $supplier->contact_person }}
                    </td>

                    <!-- Phone & Email -->
                    <td>
                        <div><i class="bi bi-telephone text-secondary me-1"></i>{{ $supplier->phone }}</div>
                        @if ($supplier->email)
                        <small class="text-muted"><i class="bi bi-envelope me-1"></i>{{ $supplier->email }}</small>
                        @endif
                    </td>

                    <!-- Category & City -->
                    <td>
                        <span class="badge text-bg-light border">{{ $supplier->category }}</span>
                        <div class="text-muted small mt-1">
                            <i class="bi bi-geo-alt me-1"></i>{{ $supplier->city ?: 'N/A' }}
                        </div>
                    </td>

                    <!-- Payment Terms -->
                    <td>
                        <span class="badge text-bg-info bg-opacity-10 text-primary border border-primary-subtle">
                            {{ $supplier->payment_terms }}
                        </span>
                    </td>

                    <!-- Payable Balance -->
                    <td class="text-end fw-bold {{ (float) $supplier->outstanding_balance > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format((float) $supplier->outstanding_balance, 2) }}
                    </td>

                    <!-- Status -->
                    <td>
                        @if ($supplier->status === 'active')
                        <span class="badge text-bg-success">Active</span>
                        @else
                        <span class="badge text-bg-secondary">Inactive</span>
                        @endif
                    </td>

                    <!-- CRUD Action Buttons -->
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <!-- View Details -->
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                title="View Supplier Profile"
                                onclick='openViewSupplierModal(@json($supplier))'>
                                <i class="bi bi-eye"></i>
                            </button>

                            <!-- Edit Supplier -->
                            <button
                                type="button"
                                class="btn btn-outline-primary"
                                title="Edit Supplier"
                                onclick='openEditSupplierModal(@json($supplier), "{{ route("admin.suppliers.update", $supplier) }}")'>
                                <i class="bi bi-pencil-square"></i>
                            </button>

                            <!-- Delete Supplier -->
                            <button
                                type="button"
                                class="btn btn-outline-danger"
                                title="Delete Supplier"
                                onclick='openDeleteSupplierModal(@json($supplier), "{{ route("admin.suppliers.destroy", $supplier) }}")'>
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-truck fs-2 d-block mb-2"></i>
                        No suppliers matched your search criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

{{-- =======================================================================
     PART 3: Supplier CRUD Modals (Add, Edit, View Details, Delete)
     ======================================================================= --}}
@push('modals')
{{-- MODAL 1: Add New Supplier (CREATE -> POST /admin/suppliers) --}}
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold">
                    <i class="bi bi-plus-circle me-2"></i>Add New Supplier
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.suppliers.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Supplier Code *</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="supplier_code" id="add_supplier_code" class="form-control" placeholder="SUP-005" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="generateSupplierCode('add_supplier_code')">Auto</button>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">Company Name *</label>
                            <input type="text" name="company_name" class="form-control form-control-sm" placeholder="e.g. Apex POS Technologies" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Status *</label>
                            <select name="status" class="form-select form-select-sm" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Contact Person *</label>
                            <input type="text" name="contact_person" class="form-control form-control-sm" placeholder="Full Name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Phone Number *</label>
                            <input type="text" name="phone" class="form-control form-control-sm" placeholder="+94 77 123 4567" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control form-control-sm" placeholder="sales@supplier.com">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Supply Category *</label>
                            <select name="category" class="form-select form-select-sm" required>
                                <option value="">-- Select Supply Category --</option>
                                @foreach ($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">City</label>
                            <input type="text" name="city" class="form-control form-control-sm" placeholder="Colombo">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tax / VAT Reg No.</label>
                            <input type="text" name="tax_number" class="form-control form-control-sm" placeholder="VAT-109283">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Payment Terms *</label>
                            <select name="payment_terms" class="form-select form-select-sm" required>
                                <option value="Net 30">Net 30 Days</option>
                                <option value="Net 15">Net 15 Days</option>
                                <option value="Cash on Delivery">Cash on Delivery (COD)</option>
                                <option value="Advance Payment">Advance Payment</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Opening Payable Balance (LKR) *</label>
                            <input type="number" step="0.01" min="0" name="outstanding_balance" class="form-control form-control-sm" value="0.00" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Office / Warehouse Address</label>
                            <textarea name="address" rows="2" class="form-control form-control-sm" placeholder="Street address, industrial zone..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold px-4">
                        <i class="bi bi-check2-circle me-1"></i> Save Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 2: Edit Supplier (UPDATE -> PUT /admin/suppliers/{supplier}) --}}
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold">
                    <i class="bi bi-pencil-square me-2"></i>Edit Supplier
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSupplierForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Supplier Code *</label>
                            <input type="text" name="supplier_code" id="edit_supplier_code" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">Company Name *</label>
                            <input type="text" name="company_name" id="edit_company_name" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Status *</label>
                            <select name="status" id="edit_status" class="form-select form-select-sm" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Contact Person *</label>
                            <input type="text" name="contact_person" id="edit_contact_person" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Phone Number *</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" name="email" id="edit_email" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Supply Category *</label>
                            <input type="text" name="category" id="edit_category" list="supplierCatList" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">City</label>
                            <input type="text" name="city" id="edit_city" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tax / VAT Reg No.</label>
                            <input type="text" name="tax_number" id="edit_tax_number" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Payment Terms *</label>
                            <select name="payment_terms" id="edit_payment_terms" class="form-select form-select-sm" required>
                                <option value="Net 30">Net 30 Days</option>
                                <option value="Net 15">Net 15 Days</option>
                                <option value="Cash on Delivery">Cash on Delivery (COD)</option>
                                <option value="Advance Payment">Advance Payment</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Outstanding Payable Balance (LKR) *</label>
                            <input type="number" step="0.01" min="0" name="outstanding_balance" id="edit_outstanding_balance" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Office / Warehouse Address</label>
                            <textarea name="address" id="edit_address" rows="2" class="form-control form-control-sm"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold px-4">
                        <i class="bi bi-save me-1"></i> Update Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 3: View Supplier Details --}}
<div class="modal fade" id="viewSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold">
                    <i class="bi bi-building me-2"></i>Supplier Profile Details
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 small">
                <h6 class="fw-bold text-dark mb-1" id="view_sup_company"></h6>
                <p class="text-muted mb-3" id="view_sup_address"></p>
                <table class="table table-sm table-bordered mb-0">
                    <tbody>
                        <tr>
                            <th class="bg-light w-50">Supplier Code</th>
                            <td><code id="view_sup_code"></code></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Contact Person</th>
                            <td class="fw-semibold" id="view_sup_contact"></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Phone Number</th>
                            <td id="view_sup_phone"></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Email</th>
                            <td id="view_sup_email"></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Category / City</th>
                            <td id="view_sup_cat_city"></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Tax / VAT Reg No.</th>
                            <td id="view_sup_tax"></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Payment Terms</th>
                            <td id="view_sup_terms"></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Payable Balance</th>
                            <td class="fw-bold text-danger" id="view_sup_balance"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 4: Delete Supplier Confirmation --}}
<div class="modal fade" id="deleteSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h6 class="modal-title fw-bold">
                    <i class="bi bi-exclamation-triangle me-2"></i>Delete Supplier
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteSupplierForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body p-4">
                    <p class="mb-1">Are you sure you want to remove this supplier from the directory?</p>
                    <div class="p-3 bg-light rounded-3 border mt-2">
                        <div class="fw-bold text-dark" id="delete_supplier_name"></div>
                        <small class="text-muted" id="delete_supplier_code"></small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm fw-semibold px-3">
                        <i class="bi bi-trash me-1"></i> Yes, Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

{{-- =======================================================================
     PART 4: Page-Specific Scripts (Injected into @stack('scripts'))
     ======================================================================= --}}
@push('scripts')
<script>
    function generateSupplierCode(inputId) {
        const rand = Math.floor(100 + Math.random() * 900);
        document.getElementById(inputId).value = 'SUP-' + rand;
    }

    function openViewSupplierModal(supplier) {
        document.getElementById('view_sup_company').textContent = supplier.company_name;
        document.getElementById('view_sup_address').textContent = supplier.address || 'No physical address recorded.';
        document.getElementById('view_sup_code').textContent = supplier.supplier_code;
        document.getElementById('view_sup_contact').textContent = supplier.contact_person;
        document.getElementById('view_sup_phone').textContent = supplier.phone;
        document.getElementById('view_sup_email').textContent = supplier.email || 'N/A';
        document.getElementById('view_sup_cat_city').textContent = supplier.category + ' • ' + (supplier.city || 'N/A');
        document.getElementById('view_sup_tax').textContent = supplier.tax_number || 'N/A';
        document.getElementById('view_sup_terms').textContent = supplier.payment_terms;
        document.getElementById('view_sup_balance').textContent = 'LKR ' + Number(supplier.outstanding_balance).toLocaleString(undefined, {
            minimumFractionDigits: 2
        });

        new bootstrap.Modal(document.getElementById('viewSupplierModal')).show();
    }

    function openEditSupplierModal(supplier, updateUrl) {
        document.getElementById('editSupplierForm').action = updateUrl;
        document.getElementById('edit_supplier_code').value = supplier.supplier_code;
        document.getElementById('edit_company_name').value = supplier.company_name;
        document.getElementById('edit_status').value = supplier.status;
        document.getElementById('edit_contact_person').value = supplier.contact_person;
        document.getElementById('edit_phone').value = supplier.phone;
        document.getElementById('edit_email').value = supplier.email || '';
        document.getElementById('edit_category').value = supplier.category;
        document.getElementById('edit_city').value = supplier.city || '';
        document.getElementById('edit_tax_number').value = supplier.tax_number || '';
        document.getElementById('edit_payment_terms').value = supplier.payment_terms;
        document.getElementById('edit_outstanding_balance').value = supplier.outstanding_balance;
        document.getElementById('edit_address').value = supplier.address || '';

        new bootstrap.Modal(document.getElementById('editSupplierModal')).show();
    }

    function openDeleteSupplierModal(supplier, deleteUrl) {
        document.getElementById('deleteSupplierForm').action = deleteUrl;
        document.getElementById('delete_supplier_name').textContent = supplier.company_name;
        document.getElementById('delete_supplier_code').textContent = 'Code: ' + supplier.supplier_code + ' | Contact: ' + supplier.contact_person;

        new bootstrap.Modal(document.getElementById('deleteSupplierModal')).show();
    }
</script>
@endpush