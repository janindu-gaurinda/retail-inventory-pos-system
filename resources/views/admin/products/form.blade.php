{{--
================================================================================
FILE PATH: resources/views/admin/products/form.blade.php
DESCRIPTION: Dedicated "Add / Edit Product" Page UI (Module 3 of ISM System).
             Extends the shared Master Layout (resources/views/layouts/admin.blade.php)
             so Sidebar, Navbar, Footer, CSS, and JS are loaded dynamically.
================================================================================

SHARED LAYOUT & PARTIALS USED:
- Master Layout: resources/views/layouts/admin.blade.php
- Sidebar Partial: resources/views/partials/sidebar.blade.php
- Navbar Partial: resources/views/partials/navbar.blade.php
- Footer Partial: resources/views/partials/footer.blade.php
- Shared CSS / JS: public/assets/css/ism-admin.css & public/assets/js/ism-admin.js

CONNECTED BACKEND & ROUTE FILES:
- Add Route (GET /admin/products/create): routes/web.php -> ProductController@create ('admin.products.create')
- Edit Route (GET /admin/products/{product}/edit): routes/web.php -> ProductController@edit ('admin.products.edit')
- Store Action (POST /admin/products): routes/web.php -> ProductController@store ('admin.products.store')
- Update Action (PUT /admin/products/{product}): routes/web.php -> ProductController@update ('admin.products.update')
- Delete Action (DELETE /admin/products/{product}): routes/web.php -> ProductController@destroy ('admin.products.destroy')
- Backend Controller: app/Http/Controllers/Admin/ProductController.php
================================================================================
--}}
@extends('layouts.admin')

{{-- =======================================================================
     PART 1: Page Header Configuration (Injected into partials/navbar.blade.php)
     ======================================================================= --}}
@section('title', ($isEdit ? 'Edit Product: ' . $product->name : 'Add New Product') . ' | ISM System')
@section('page_title', $isEdit ? 'Edit Product' : 'Add New Product')
@section('page_badge')
@if ($isEdit)
<span class="badge text-bg-warning">Editing: {{ $product->sku }}</span>
@else
<span class="badge text-bg-primary">Create Mode</span>
@endif
@endsection
@section('page_subtitle', $isEdit
? 'Update existing product details, pricing, barcode, or stock quantity'
: 'Create a new inventory item with SKU, barcode, cost & selling price'
)

@section('topbar_actions')
@if ($isEdit)
<a href="{{ route('admin.products.create') }}" class="btn btn-outline-primary btn-sm fw-semibold">
    <i class="bi bi-plus-lg me-1"></i> Switch to Add New
</a>
@endif
<a href="{{ route('admin.products.index') }}" class="btn btn-light border btn-sm fw-semibold">
    <i class="bi bi-arrow-left me-1"></i> Back to Products List
</a>
@endsection

{{-- =======================================================================
     PART 2: Main Add / Edit Product Content (Injected into layouts/admin.blade.php)
     ======================================================================= --}}
@section('content')

<div class="row g-4">

    {{-- SECTION A: Left Column — Main Add / Edit Product Form --}}
    <div class="col-12 col-xl-8">
        <form
            action="{{ $isEdit ? route('admin.products.update', $product) : route('admin.products.store') }}"
            method="POST"
            id="productForm">
            @csrf
            @if ($isEdit)
            @method('PUT')
            @endif

            <!-- Step 1: Basic Identification & Classification -->
            <div class="content-card p-3 p-sm-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <span class="section-badge">1</span>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Basic Information &amp; Identification</h6>
                        <small class="text-muted">Product name, SKU code, EAN-13 barcode, category &amp; supplier</small>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-8">
                        <label for="name" class="form-label small fw-semibold">Product Name <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $product->name ?? '') }}"
                            placeholder="e.g. Wireless Barcode Scanner 2D"
                            required>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="status" class="form-label small fw-semibold">Product Status <span class="text-danger">*</span></label>
                        <select id="status" name="status" class="form-select" required>
                            @php $currentStatus = old('status', $product->status ?? 'active'); @endphp
                            <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Active (Available in POS)</option>
                            <option value="inactive" {{ $currentStatus === 'inactive' ? 'selected' : '' }}>Inactive (Hidden in POS)</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="sku" class="form-label small fw-semibold">SKU Code (Stock Keeping Unit) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-hash"></i></span>
                            <input
                                type="text"
                                id="sku"
                                name="sku"
                                class="form-control @error('sku') is-invalid @enderror"
                                value="{{ old('sku', $product->sku ?? '') }}"
                                placeholder="ISM-PRD-0601"
                                required>
                            <button type="button" class="btn btn-outline-secondary" onclick="generateSku('sku')">
                                <i class="bi bi-magic me-1"></i>Auto SKU
                            </button>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="barcode" class="form-label small fw-semibold">Barcode Number (EAN-13 / UPC) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-upc-scan"></i></span>
                            <input
                                type="text"
                                id="barcode"
                                name="barcode"
                                class="form-control font-monospace @error('barcode') is-invalid @enderror"
                                value="{{ old('barcode', $product->barcode ?? '') }}"
                                placeholder="8901041006010"
                                required>
                            <button type="button" class="btn btn-outline-secondary" onclick="generateBarcode('barcode')">
                                <i class="bi bi-magic me-1"></i>Auto Barcode
                            </button>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="category" class="form-label small fw-semibold mb-0">
                                Category <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex gap-2">
                                <button type="button"
                                    class="btn btn-link btn-sm p-0 text-decoration-none fw-semibold"
                                    style="font-size: 0.78rem;"
                                    data-bs-toggle="modal"
                                    data-bs-target="#quickAddCategoryModal">
                                    <i class="bi bi-plus-circle-fill me-1"></i>New Category
                                </button>
                                <a href="{{ route('admin.categories.index') }}"
                                    class="text-secondary text-decoration-none"
                                    style="font-size: 0.78rem;"
                                    title="Manage All Categories">
                                    <i class="bi bi-gear"></i>
                                </a>
                            </div>
                        </div>
                        @php
                        $selectedCategory = old('category', session('selected_category', $product->category ?? ''));
                        @endphp
                        <select
                            id="category"
                            name="category"
                            class="form-select @error('category') is-invalid @enderror"
                            required>
                            <option value="">-- Select Category from Database --</option>
                            @foreach ($categories as $cat)
                            <option value="{{ $cat }}" {{ $selectedCategory === $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                            @endforeach
                        </select>
                        @if ($categories->isEmpty())
                        <small class="text-warning d-block mt-1">
                            <i class="bi bi-exclamation-circle me-1"></i>No categories in database yet — click <strong>+ New Category</strong> above to add one.
                        </small>
                        @endif
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="supplier" class="form-label small fw-semibold mb-0">
                                Supplier Name
                            </label>
                            <div class="d-flex gap-2">
                                <button type="button"
                                    class="btn btn-link btn-sm p-0 text-decoration-none fw-semibold"
                                    style="font-size: 0.78rem;"
                                    data-bs-toggle="modal"
                                    data-bs-target="#quickAddSupplierModal">
                                    <i class="bi bi-plus-circle-fill me-1"></i>New Supplier
                                </button>
                                <a href="{{ route('admin.suppliers.index') }}"
                                    class="text-secondary text-decoration-none"
                                    style="font-size: 0.78rem;"
                                    title="Manage All Suppliers">
                                    <i class="bi bi-gear"></i>
                                </a>
                            </div>
                        </div>
                        @php
                        $selectedSupplier = old('supplier', session('selected_supplier', $product->supplier ?? ''));
                        @endphp
                        <select
                            id="supplier"
                            name="supplier"
                            class="form-select @error('supplier') is-invalid @enderror">
                            <option value="">-- Select Supplier from Database (Optional) --</option>
                            @foreach ($suppliers as $sup)
                            <option value="{{ $sup }}" {{ $selectedSupplier === $sup ? 'selected' : '' }}>
                                {{ $sup }}
                            </option>
                            @endforeach
                        </select>
                        @if ($suppliers->isEmpty())
                        <small class="text-muted d-block mt-1">
                            <i class="bi bi-info-circle me-1"></i>No suppliers in database yet — click <strong>+ New Supplier</strong> above to add one.
                        </small>
                        @endif
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label small fw-semibold">Product Description / Notes</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="2"
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Enter technical specifications, warranty notes, or shelf location...">{{ old('description', $product->description ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Step 2: Pricing & Profit Margin -->
            <div class="content-card p-3 p-sm-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="section-badge">2</span>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Cost &amp; Selling Price Configuration</h6>
                            <small class="text-muted">Cost price is visible to Admin, Store Manager &amp; Stock Manager only</small>
                        </div>
                    </div>
                    <span class="badge text-bg-success" id="liveMarginBadge">Profit Margin: 0.0%</span>
                </div>
                <!-- 1. Cost Price Input -->
                <div class="row g-3">
                    <div class="col-12 col-md-3">
                        <label for="cost_price" class="form-label small fw-semibold">Cost Price (LKR) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">LKR</span>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                id="cost_price"
                                name="cost_price"
                                class="form-control @error('cost_price') is-invalid @enderror"
                                value="{{ old('cost_price', $product->cost_price ?? '0.00') }}"
                                required>
                        </div>
                    </div>
                    <!-- 2. NEW: Profit Percentage (%) Input -->
                    <div class="col-12 col-md-3">
                        <label for="profit_percent" class="form-label small fw-semibold text-primary">
                            Profit Percentage (%)
                        </label>
                        <div class="input-group">
                            <input
                                type="number"
                                step="0.01"
                                id="profit_percent"
                                class="form-control"
                                placeholder="e.g. 25">
                            <span class="input-group-text bg-light">%</span>
                        </div>
                    </div>
                    <!-- 3. Selling Price Input -->
                    <div class="col-12 col-md-3">
                        <label for="selling_price" class="form-label small fw-semibold">Selling Price (LKR) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">LKR</span>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                id="selling_price"
                                name="selling_price"
                                class="form-control @error('selling_price') is-invalid @enderror"
                                value="{{ old('selling_price', $product->selling_price ?? '0.00') }}"
                                required>
                        </div>
                    </div>
                    <!-- 4. Estimated Profit / Unit Display Box -->
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold text-secondary">Estimated Profit / Unit</label>
                        <div class="p-2 px-3 rounded-3 border bg-light d-flex justify-content-between align-items-center" style="height: 38px;">
                            <span class="small text-muted">Net Profit:</span>
                            <span class="fw-bold text-success" id="liveUnitProfit">LKR 0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Stock & Inventory Thresholds -->
            <div class="content-card p-3 p-sm-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <span class="section-badge">3</span>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Inventory Stock &amp; Reorder Alert Levels</h6>
                        <small class="text-muted">Set current on-hand quantity, unit of measure, and low-stock alert threshold</small>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="stock_quantity" class="form-label small fw-semibold">
                            {{ $isEdit ? 'Current Stock Quantity' : 'Opening Stock Quantity' }} <span class="text-danger">*</span>
                        </label>
                        <input
                            type="number"
                            min="0"
                            id="stock_quantity"
                            name="stock_quantity"
                            class="form-control @error('stock_quantity') is-invalid @enderror"
                            value="{{ old('stock_quantity', $product->stock_quantity ?? 10) }}"
                            required>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="min_stock_level" class="form-label small fw-semibold">Low Stock Alert Level <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            min="0"
                            id="min_stock_level"
                            name="min_stock_level"
                            class="form-control @error('min_stock_level') is-invalid @enderror"
                            value="{{ old('min_stock_level', $product->min_stock_level ?? 5) }}"
                            required>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="unit" class="form-label small fw-semibold">Unit of Measure <span class="text-danger">*</span></label>
                        @php $currentUnit = old('unit', $product->unit ?? 'pcs'); @endphp
                        <select id="unit" name="unit" class="form-select" required>
                            <option value="pcs" {{ $currentUnit === 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                            <option value="box" {{ $currentUnit === 'box' ? 'selected' : '' }}>Box</option>
                            <option value="roll" {{ $currentUnit === 'roll' ? 'selected' : '' }}>Roll</option>
                            <option value="pack" {{ $currentUnit === 'pack' ? 'selected' : '' }}>Pack</option>
                            <option value="kg" {{ $currentUnit === 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Form Submit & Action Buttons -->
            <div class="content-card p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>

                <div class="d-flex flex-wrap gap-2">
                    @if (! $isEdit)
                    <button type="submit" name="save_and_new" value="1" class="btn btn-outline-primary fw-semibold">
                        <i class="bi bi-plus-circle me-1"></i> Save &amp; Add Another
                    </button>
                    @endif
                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                        <i class="bi bi-check2-circle me-1"></i>
                        {{ $isEdit ? 'Update Product' : 'Save Product' }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- SECTION B: Right Column — Live Barcode Label Preview & Quick Edit Switcher --}}
    <div class="col-12 col-xl-4">

        <div class="content-card p-3 p-sm-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-upc-scan text-primary me-1"></i> Live Label Preview
                </h6>
                <button type="button" class="btn btn-sm btn-outline-dark" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </div>

            <div class="barcode-preview-box p-3 text-center">
                <div class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.08em;">
                    {{ $systemSettings['store_name'] ?? 'ISM Retail Store' }}
                </div>
                <div class="fw-bold text-dark my-1 text-truncate" id="previewName">
                    {{ $product->name ?? 'Product Name Preview' }}
                </div>
                <div class="py-2 px-3 bg-white border rounded my-2 font-monospace fw-bold" style="letter-spacing: 0.2em;" id="previewBarcode">
                    ||{{ $product->barcode ?? '8901041000000' }}||
                </div>
                <div class="fw-bold text-primary fs-5" id="previewPrice">
                    LKR {{ number_format((float) ($product->selling_price ?? 0), 2) }}
                </div>
                <div class="text-muted small" id="previewSku">
                    SKU: {{ $product->sku ?? 'ISM-PRD-XXXX' }}
                </div>
            </div>

            <div class="mt-3 pt-3 border-top small">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Total Cost Value:</span>
                    <span class="fw-semibold text-dark" id="previewTotalCost">LKR 0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Total Retail Value:</span>
                    <span class="fw-semibold text-primary" id="previewTotalRetail">LKR 0.00</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Expected Total Profit:</span>
                    <span class="fw-bold text-success" id="previewTotalProfit">LKR 0.00</span>
                </div>
            </div>
        </div>

        <div class="content-card p-3 p-sm-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-pencil-square text-primary me-1"></i> Quick Edit Existing Product
                </h6>
                <span class="badge text-bg-light border">Click to Edit</span>
            </div>
            <p class="text-muted small mb-3">
                Select any product below to load it into this form for editing:
            </p>

            <div class="d-grid gap-2">
                @foreach ($recentProducts as $item)
                <a href="{{ route('admin.products.edit', $item) }}"
                    class="quick-product-item {{ ($isEdit && $product->id === $item->id) ? 'active-item' : '' }}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-truncate me-2">
                            <div class="fw-semibold small text-truncate">{{ $item->name }}</div>
                            <small class="text-muted"><code>{{ $item->sku }}</code> &bull; Stock: {{ $item->stock_quantity }}</small>
                        </div>
                        <span class="badge text-bg-light border">
                            <i class="bi bi-pencil"></i> Edit
                        </span>
                    </div>
                </a>
                @endforeach
            </div>

            @if ($isEdit)
            <hr class="my-3">
            <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                onsubmit="return confirm('Are you sure you want to permanently delete this product?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                    <i class="bi bi-trash me-1"></i> Delete This Product
                </button>
            </form>
            @endif
        </div>

    </div>

</div>

@endsection

{{-- =======================================================================
     PART 2B: Inline Modals for Adding New Category & New Supplier to Database
              Accessible by Admin, Store Manager, and Stock Manager
     ======================================================================= --}}
@push('modals')
{{-- FORM MODAL 1: Quick Add New Category to 'categories' table --}}
<div class="modal fade" id="quickAddCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title fw-bold">
                        <i class="bi bi-tags-fill me-2"></i>Add New Product Category
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small mb-3">
                        <i class="bi bi-shield-check me-1"></i>
                        <strong>Role Access:</strong> Admin, Store Manager &amp; Stock Manager can create and manage categories.
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category Code <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="category_code" id="quick_cat_code" class="form-control font-monospace"
                                value="CAT-{{ rand(100, 999) }}" required maxlength="40">
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="document.getElementById('quick_cat_code').value = 'CAT-' + Math.floor(100 + Math.random() * 900)">
                                Auto
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required maxlength="120"
                            placeholder="e.g., POS Hardware, Consumables, Beverages">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select form-select-sm" required>
                            <option value="active" selected>Active (Visible in Dropdowns)</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Description (Optional)</label>
                        <textarea name="description" rows="2" class="form-control form-control-sm"
                            placeholder="Short description of items in this category..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold px-3">
                        <i class="bi bi-check2-circle me-1"></i> Save Category to Database
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- FORM MODAL 2: Quick Add New Supplier to 'suppliers' table --}}
<div class="modal fade" id="quickAddSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.suppliers.store') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                <div class="modal-header bg-dark text-white">
                    <h6 class="modal-title fw-bold">
                        <i class="bi bi-truck me-2 text-info"></i>Add New Supplier to Database
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small mb-3">
                        <i class="bi bi-shield-check me-1"></i>
                        <strong>Role Access:</strong> Admin, Store Manager &amp; Stock Manager can register and link suppliers.
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Supplier Code <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="supplier_code" id="quick_sup_code" class="form-control font-monospace"
                                    value="SUP-{{ rand(100, 999) }}" required maxlength="40">
                                <button type="button" class="btn btn-outline-secondary"
                                    onclick="document.getElementById('quick_sup_code').value = 'SUP-' + Math.floor(100 + Math.random() * 900)">
                                    Auto
                                </button>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Company / Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control form-control-sm" required maxlength="255"
                                placeholder="e.g., Apex POS Technologies">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Contact Person <span class="text-danger">*</span></label>
                            <input type="text" name="contact_person" class="form-control form-control-sm" required maxlength="150"
                                placeholder="e.g., Kasun Perera">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control form-control-sm" required maxlength="40"
                                placeholder="e.g., +94 11 234 5678">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control form-control-sm" maxlength="150"
                                placeholder="sales@supplier.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Supply Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select form-select-sm" required>
                                <option value="">-- Select Supply Category --</option>
                                @foreach ($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Payment Terms <span class="text-danger">*</span></label>
                            <select name="payment_terms" class="form-select form-select-sm" required>
                                <option value="Net 30">Net 30 Days</option>
                                <option value="Net 15">Net 15 Days</option>
                                <option value="Cash on Delivery">Cash on Delivery</option>
                                <option value="Advance Payment">Advance Payment</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Opening Balance (LKR) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="outstanding_balance" class="form-control form-control-sm" value="0.00" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select form-select-sm" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold px-3">
                        <i class="bi bi-check2-circle me-1"></i> Save Supplier to Database
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

{{-- =======================================================================
     PART 3: Page-Specific Scripts (Injected into @stack('scripts') in Layout)
     ======================================================================= --}}
@push('scripts')
<script>
    const nameInput = document.getElementById('name');
    const skuInput = document.getElementById('sku');
    const barcodeInput = document.getElementById('barcode');
    const costInput = document.getElementById('cost_price');
    const profitPctInput = document.getElementById('profit_percent'); // NEW input reference
    const sellingInput = document.getElementById('selling_price');
    const stockInput = document.getElementById('stock_quantity');

    // 1. When user types in Profit % -> Auto-calculate Selling Price
    if (profitPctInput) {
        profitPctInput.addEventListener('input', function() {
            const cost = parseFloat(costInput.value) || 0;
            const pct = parseFloat(profitPctInput.value);

            if (!isNaN(pct) && cost > 0) {
                // Formula: Selling Price = Cost + (Cost * Pct / 100)
                const calculatedSelling = cost + (cost * (pct / 100));
                sellingInput.value = calculatedSelling.toFixed(2);
            }
            updateLivePreview();
        });
    }

    // 2. When user types in Selling Price -> Auto-calculate Profit %
    if (sellingInput) {
        sellingInput.addEventListener('input', function() {
            const cost = parseFloat(costInput.value) || 0;
            const selling = parseFloat(sellingInput.value) || 0;

            if (cost > 0 && selling > 0) {
                // Formula: Profit % = ((Selling - Cost) / Cost) * 100
                const calculatedPct = ((selling - cost) / cost) * 100;
                profitPctInput.value = calculatedPct.toFixed(2);
            } else {
                profitPctInput.value = '';
            }
            updateLivePreview();
        });
    }

    // 3. When user changes Cost Price -> Recalculate Selling Price if Profit % is filled, otherwise update Profit %
    if (costInput) {
        costInput.addEventListener('input', function() {
            const cost = parseFloat(costInput.value) || 0;
            const pct = parseFloat(profitPctInput.value);
            const selling = parseFloat(sellingInput.value) || 0;

            if (!isNaN(pct) && pct > 0 && cost > 0) {
                // Keep the user's chosen % and update Selling Price
                sellingInput.value = (cost + (cost * (pct / 100))).toFixed(2);
            } else if (cost > 0 && selling > 0) {
                // Otherwise update the % based on existing Selling Price
                profitPctInput.value = (((selling - cost) / cost) * 100).toFixed(2);
            }
            updateLivePreview();
        });
    }

    // 4. Master function that updates Net Profit, Top Green Badge, and Right-side Label Preview
    function updateLivePreview() {
        const nameVal = nameInput.value.trim() || 'Product Name Preview';
        const skuVal = skuInput.value.trim() || 'ISM-PRD-XXXX';
        const barcodeVal = barcodeInput.value.trim() || '8901041000000';
        const cost = parseFloat(costInput.value) || 0;
        const selling = parseFloat(sellingInput.value) || 0;
        const stock = parseInt(stockInput.value, 10) || 0;

        const unitProfit = selling - cost;
        // Margin % shown on the green badge:
        const marginPct = cost > 0 ? ((unitProfit / cost) * 100).toFixed(1) : '0.0';

        document.getElementById('liveUnitProfit').textContent =
            'LKR ' + unitProfit.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

        const marginBadge = document.getElementById('liveMarginBadge');
        marginBadge.textContent = 'Profit Margin: ' + marginPct + '%';
        marginBadge.className = 'badge ' + (unitProfit >= 0 ? 'text-bg-success' : 'text-bg-danger');

        // Update Right-Side Barcode Label Preview
        document.getElementById('previewName').textContent = nameVal;
        document.getElementById('previewSku').textContent = 'SKU: ' + skuVal;
        document.getElementById('previewBarcode').textContent = '||' + barcodeVal + '||';
        document.getElementById('previewPrice').textContent =
            'LKR ' + selling.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

        const totalCost = cost * Math.max(0, stock);
        const totalRetail = selling * Math.max(0, stock);
        const totalProfit = totalRetail - totalCost;

        document.getElementById('previewTotalCost').textContent =
            'LKR ' + totalCost.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        document.getElementById('previewTotalRetail').textContent =
            'LKR ' + totalRetail.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        document.getElementById('previewTotalProfit').textContent =
            'LKR ' + totalProfit.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
    }

    // Listen to Name, SKU, Barcode, and Stock inputs for the right-side preview
    [nameInput, skuInput, barcodeInput, stockInput].forEach(el => {
        if (el) el.addEventListener('input', updateLivePreview);
    });

    // Initialize Profit % when opening an existing product in Edit mode
    if (costInput && sellingInput && profitPctInput) {
        const initCost = parseFloat(costInput.value) || 0;
        const initSelling = parseFloat(sellingInput.value) || 0;
        if (initCost > 0 && initSelling > 0) {
            profitPctInput.value = (((initSelling - initCost) / initCost) * 100).toFixed(2);
        }
    }

    updateLivePreview();
</script>
@endpush