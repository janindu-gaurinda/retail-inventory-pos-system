{{--
================================================================================
FILE PATH: resources/views/admin/products/index.blade.php
DESCRIPTION: Products List Page UI with Full CRUD.
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
- Route (GET /admin/products): routes/web.php -> ProductController@index ('admin.products.index')
- Route (POST /admin/products): routes/web.php -> ProductController@store ('admin.products.store')
- Route (PUT /admin/products/{product}): routes/web.php -> ProductController@update ('admin.products.update')
- Route (DELETE /admin/products/{product}): routes/web.php -> ProductController@destroy ('admin.products.destroy')
- Backend Controller: app/Http/Controllers/Admin/ProductController.php
================================================================================
--}}
@extends('layouts.admin')

@php
    $canManage = auth()->user()->canManageProducts();
    $canViewCost = auth()->user()->canViewCostPrice();
@endphp

{{-- =======================================================================
     PART 1: Page Header Configuration (Injected into partials/navbar.blade.php)
     ======================================================================= --}}
@section('title', 'Products List (CRUD) | ISM System')
@section('page_title', 'Products List')
@section('page_badge')
    @if ($canManage)
        <span class="badge text-bg-success bg-opacity-10 text-success border border-success-subtle">
            View &amp; Edit (Full CRUD)
        </span>
    @else
        <span class="badge text-bg-secondary">
            View Only (No Cost Price)
        </span>
    @endif
@endsection
@section('page_subtitle', 'Manage product catalog, SKUs, barcodes, stock levels & pricing')

@section('topbar_actions')
    @if ($canManage)
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm fw-semibold d-inline-flex align-items-center gap-1 px-3 py-2 rounded-3">
            <i class="bi bi-plus-lg"></i> Add New Product
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm fw-semibold d-none d-md-inline-flex align-items-center gap-1 px-3 py-2 rounded-3"
                data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="bi bi-window-plus"></i> Quick Add Modal
        </button>
    @endif
@endsection

{{-- =======================================================================
     PART 2: Main Products List Content (Injected into layouts/admin.blade.php)
     ======================================================================= --}}
@section('content')

    {{-- SECTION A: Inventory Summary KPI Cards ($summary from ProductController@index) --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-medium">Total Products</div>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($summary['total_products'] ?? 0) }}</h4>
                    <small class="text-muted">{{ $summary['total_categories'] ?? 0 }} Categories</small>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
                <div>
                    @if ($canViewCost)
                        <div class="text-secondary small fw-medium">Total Stock Cost Value</div>
                        <h5 class="fw-bold mb-0 text-dark">LKR {{ number_format($summary['total_cost_value'] ?? 0, 2) }}</h5>
                        <small class="text-success fw-medium">Retail: LKR {{ number_format($summary['total_retail_value'] ?? 0, 0) }}</small>
                    @else
                        <div class="text-secondary small fw-medium">Total Retail Value</div>
                        <h5 class="fw-bold mb-0 text-dark">LKR {{ number_format($summary['total_retail_value'] ?? 0, 2) }}</h5>
                        <small class="text-muted"><i class="bi bi-lock-fill"></i> Cost price hidden</small>
                    @endif
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-medium">Low Stock Alerts</div>
                    <h4 class="fw-bold mb-0 text-warning">{{ $summary['low_stock_count'] ?? 0 }}</h4>
                    <small class="text-muted">At or below minimum level</small>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-exclamation-circle-fill"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-medium">Out of Stock</div>
                    <h4 class="fw-bold mb-0 text-danger">{{ $summary['out_of_stock_count'] ?? 0 }}</h4>
                    <small class="text-muted">Requires immediate PO</small>
                </div>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-x-octagon-fill"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION B: Search & Filter Bar (GET -> route('admin.products.index')) --}}
    <div class="content-card p-3 mb-4">
        <form action="{{ route('admin.products.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label small fw-semibold text-secondary mb-1">Search Product / SKU / Barcode</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        class="form-control"
                        placeholder="Search by product name, SKU, barcode, or supplier..."
                    >
                </div>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Category</label>
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
                <label class="form-label small fw-semibold text-secondary mb-1">Stock Status</label>
                <select name="stock_status" class="form-select form-select-sm">
                    <option value="">All Stock Levels</option>
                    <option value="in_stock" {{ ($filters['stock_status'] ?? '') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="low_stock" {{ ($filters['stock_status'] ?? '') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out_of_stock" {{ ($filters['stock_status'] ?? '') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- SECTION C: Products List Table --}}
    <div class="content-card p-3 p-sm-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-table text-primary me-1"></i> Product Inventory Catalog ({{ $products->count() }} items)
                </h6>
                <small class="text-muted">
                    @if ($canViewCost)
                        Showing full product details including Cost Price &amp; Profit Margin
                    @else
                        Cashier View: Cost Price &amp; Edit actions are restricted
                    @endif
                </small>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Product / SKU</th>
                        <th>Barcode</th>
                        <th>Category / Supplier</th>
                        @if ($canViewCost)
                            <th class="text-end">Cost Price</th>
                        @endif
                        <th class="text-end">Selling Price</th>
                        <th class="text-center">Stock</th>
                        <th>Stock Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        @php
                            $badge = $product->stockBadge();
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="product-icon-box">
                                        <i class="bi bi-box"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $product->name }}</div>
                                        <div>
                                            <code class="text-primary">{{ $product->sku }}</code>
                                            @if ($product->status === 'inactive')
                                                <span class="badge text-bg-secondary ms-1">Inactive</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="badge text-bg-light border font-monospace">
                                    <i class="bi bi-upc me-1"></i>{{ $product->barcode }}
                                </span>
                            </td>

                            <td>
                                <div class="fw-medium text-dark">{{ $product->category }}</div>
                                <small class="text-muted">{{ $product->supplier ?: 'N/A' }}</small>
                            </td>

                            @if ($canViewCost)
                                <td class="text-end">
                                    <div class="fw-medium text-secondary">LKR {{ number_format((float) $product->cost_price, 2) }}</div>
                                    <small class="text-success">Margin: {{ $product->profitMarginPercent() }}%</small>
                                </td>
                            @endif

                            <td class="text-end fw-bold text-dark">
                                LKR {{ number_format((float) $product->selling_price, 2) }}
                            </td>

                            <td class="text-center">
                                <span class="fw-bold fs-6 {{ $product->stock_quantity <= 0 ? 'text-danger' : ($product->stock_quantity <= $product->min_stock_level ? 'text-warning' : 'text-dark') }}">
                                    {{ $product->stock_quantity }}
                                </span>
                                <div class="text-muted" style="font-size: 0.72rem;">
                                    {{ $product->unit }} (Min: {{ $product->min_stock_level }})
                                </div>
                            </td>

                            <td>
                                <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                            </td>

                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        title="View Details"
                                        onclick='openViewModal(@json($product), "{{ $badge["label"] }}")'
                                    >
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    @if ($canManage)
                                        <a
                                            href="{{ route('admin.products.barcodes', ['product_id' => $product->id]) }}"
                                            class="btn btn-outline-dark"
                                            title="Open Barcode / Label Print Page"
                                        >
                                            <i class="bi bi-upc-scan"></i>
                                        </a>

                                        <a
                                            href="{{ route('admin.products.edit', $product) }}"
                                            class="btn btn-outline-primary"
                                            title="Open Full Edit Product Page"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <button
                                            type="button"
                                            class="btn btn-outline-danger"
                                            title="Delete Product"
                                            onclick='openDeleteModal(@json($product), "{{ route("admin.products.destroy", $product) }}")'
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canViewCost ? 8 : 7 }}" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No products matched your search criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

{{-- =======================================================================
     PART 3: Page-Specific Modals (Injected into @stack('modals') in Layout)
     ======================================================================= --}}
@push('modals')
    @if ($canManage)
        {{-- MODAL 1: Quick Add New Product --}}
        <div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-primary text-white">
                        <h6 class="modal-title fw-bold">
                            <i class="bi bi-plus-circle me-2"></i>Quick Add Product
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('admin.products.store') }}" method="POST">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">SKU Code *</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="sku" id="add_sku" class="form-control" placeholder="ISM-PRD-0601" required>
                                        <button type="button" class="btn btn-outline-secondary" onclick="generateSku('add_sku')">Auto</button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Barcode (EAN-13) *</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="barcode" id="add_barcode" class="form-control" placeholder="8901041006010" required>
                                        <button type="button" class="btn btn-outline-secondary" onclick="generateBarcode('add_barcode')">Auto</button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Status *</label>
                                    <select name="status" class="form-select form-select-sm" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label small fw-semibold">Product Name *</label>
                                    <input type="text" name="name" class="form-control form-control-sm" placeholder="Enter full product name" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Unit *</label>
                                    <select name="unit" class="form-select form-select-sm" required>
                                        <option value="pcs">Pieces (pcs)</option>
                                        <option value="box">Box</option>
                                        <option value="roll">Roll</option>
                                        <option value="pack">Pack</option>
                                        <option value="kg">Kilogram (kg)</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label small fw-semibold mb-0">Category *</label>
                                        <a href="{{ route('admin.categories.index') }}" class="small text-decoration-none">+ Manage Categories</a>
                                    </div>
                                    <select name="category" class="form-select form-select-sm" required>
                                        <option value="">-- Select Category --</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat }}">{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label small fw-semibold mb-0">Supplier Name</label>
                                        <a href="{{ route('admin.suppliers.index') }}" class="small text-decoration-none">+ Manage Suppliers</a>
                                    </div>
                                    <select name="supplier" class="form-select form-select-sm">
                                        <option value="">-- Select Supplier (Optional) --</option>
                                        @foreach ($suppliers ?? [] as $sup)
                                            <option value="{{ $sup }}">{{ $sup }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Cost Price (LKR) *</label>
                                    <input type="number" step="0.01" min="0" name="cost_price" class="form-control form-control-sm" placeholder="0.00" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Selling Price (LKR) *</label>
                                    <input type="number" step="0.01" min="0" name="selling_price" class="form-control form-control-sm" placeholder="0.00" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Initial Stock *</label>
                                    <input type="number" min="0" name="stock_quantity" class="form-control form-control-sm" value="10" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Min Stock Alert *</label>
                                    <input type="number" min="0" name="min_stock_level" class="form-control form-control-sm" value="5" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Description</label>
                                    <textarea name="description" rows="2" class="form-control form-control-sm" placeholder="Optional product specifications or notes..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm fw-semibold px-4">
                                <i class="bi bi-check2-circle me-1"></i> Save Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL 2: Barcode Label Preview --}}
        <div class="modal fade" id="barcodeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content border-0 shadow text-center">
                    <div class="modal-header bg-light">
                        <h6 class="modal-title fw-bold"><i class="bi bi-upc-scan me-1"></i> Barcode Label Preview</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="border rounded-3 p-3 bg-white">
                            <div class="fw-bold small text-uppercase text-muted mb-1">{{ $systemSettings['store_name'] ?? 'ISM Retail Store' }}</div>
                            <div class="fw-semibold text-dark small mb-2" id="bc_name"></div>
                            <div class="py-2 px-3 bg-light border rounded mb-2" style="letter-spacing: 0.25em; font-family: monospace; font-weight: 700;" id="bc_code"></div>
                            <div class="fw-bold text-primary fs-6" id="bc_price"></div>
                            <small class="text-muted" id="bc_sku"></small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light justify-content-center">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-dark btn-sm" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Print Label
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL 3: Delete Confirmation --}}
        <div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white">
                        <h6 class="modal-title fw-bold">
                            <i class="bi bi-exclamation-triangle me-2"></i>Delete Product
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="deleteProductForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-body p-4">
                            <p class="mb-1">Are you sure you want to permanently delete this product?</p>
                            <div class="p-3 bg-light rounded-3 border mt-2">
                                <div class="fw-bold text-dark" id="delete_product_name"></div>
                                <small class="text-muted" id="delete_product_sku"></small>
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
    @endif

    {{-- MODAL 4: View Product Details --}}
    <div class="modal fade" id="viewProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title fw-bold">
                        <i class="bi bi-box-seam me-2"></i>Product Details
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 small">
                    <h6 class="fw-bold text-dark mb-1" id="view_name"></h6>
                    <p class="text-muted mb-3" id="view_description"></p>
                    <table class="table table-sm table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th class="bg-light w-50">SKU</th>
                                <td><code id="view_sku"></code></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Barcode</th>
                                <td class="font-monospace" id="view_barcode"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Category</th>
                                <td id="view_category"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Supplier</th>
                                <td id="view_supplier"></td>
                            </tr>
                            @if ($canViewCost)
                                <tr>
                                    <th class="bg-light">Cost Price</th>
                                    <td class="fw-semibold text-secondary" id="view_cost_price"></td>
                                </tr>
                            @endif
                            <tr>
                                <th class="bg-light">Selling Price</th>
                                <td class="fw-bold text-primary" id="view_selling_price"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Current Stock</th>
                                <td id="view_stock"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Stock Status</th>
                                <td><span class="badge text-bg-primary" id="view_stock_badge"></span></td>
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
@endpush

{{-- =======================================================================
     PART 4: Page-Specific Scripts (Injected into @stack('scripts') in Layout)
     ======================================================================= --}}
@push('scripts')
<script>
    function openViewModal(product, stockLabel) {
        document.getElementById('view_name').textContent = product.name;
        document.getElementById('view_description').textContent = product.description || 'No description provided.';
        document.getElementById('view_sku').textContent = product.sku;
        document.getElementById('view_barcode').textContent = product.barcode;
        document.getElementById('view_category').textContent = product.category;
        document.getElementById('view_supplier').textContent = product.supplier || 'N/A';

        const costEl = document.getElementById('view_cost_price');
        if (costEl) {
            costEl.textContent = 'LKR ' + Number(product.cost_price).toLocaleString(undefined, {minimumFractionDigits: 2});
        }

        document.getElementById('view_selling_price').textContent = 'LKR ' + Number(product.selling_price).toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('view_stock').textContent = product.stock_quantity + ' ' + product.unit + ' (Min: ' + product.min_stock_level + ')';
        document.getElementById('view_stock_badge').textContent = stockLabel;

        new bootstrap.Modal(document.getElementById('viewProductModal')).show();
    }

    function openBarcodeModal(product) {
        document.getElementById('bc_name').textContent = product.name;
        document.getElementById('bc_code').textContent = '||' + product.barcode + '||';
        document.getElementById('bc_price').textContent = 'LKR ' + Number(product.selling_price).toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('bc_sku').textContent = 'SKU: ' + product.sku;

        new bootstrap.Modal(document.getElementById('barcodeModal')).show();
    }

    function openDeleteModal(product, deleteUrl) {
        document.getElementById('deleteProductForm').action = deleteUrl;
        document.getElementById('delete_product_name').textContent = product.name;
        document.getElementById('delete_product_sku').textContent = 'SKU: ' + product.sku + ' | Barcode: ' + product.barcode;

        new bootstrap.Modal(document.getElementById('deleteProductModal')).show();
    }
</script>
@endpush
