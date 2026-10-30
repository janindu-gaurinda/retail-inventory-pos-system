{{--
================================================================================
FILE PATH: resources/views/admin/purchase_orders/index.blade.php
DESCRIPTION: Purchase Orders (POs) Page UI (Module 7 of ISM System).
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
- Route (GET /admin/purchase-orders): routes/web.php -> PurchaseOrderController@index ('admin.purchase-orders.index')
- Route (POST /admin/purchase-orders): routes/web.php -> PurchaseOrderController@store ('admin.purchase-orders.store')
- Route (PATCH .../receive): routes/web.php -> PurchaseOrderController@receive ('admin.purchase-orders.receive')
- Route (PATCH .../cancel): routes/web.php -> PurchaseOrderController@cancel ('admin.purchase-orders.cancel')
- Route (DELETE .../{purchaseOrder}): routes/web.php -> PurchaseOrderController@destroy ('admin.purchase-orders.destroy')
- Backend Controller: app/Http/Controllers/Admin/PurchaseOrderController.php
================================================================================
--}}
@extends('layouts.admin')

{{-- =======================================================================
     PART 1: Dynamic Navbar Header Sections (Injected into partials/navbar.blade.php)
     ======================================================================= --}}
@section('title', 'Purchase Orders (POs) | ISM System')
@section('page_title', 'Purchase Orders (POs)')
@section('page_badge')
    <span class="badge text-bg-primary bg-opacity-10 text-primary border border-primary-subtle">
        Create &amp; Receive Stock
    </span>
@endsection
@section('page_subtitle', 'Create supplier purchase orders, track deliveries, and receive incoming stock into inventory')

@section('topbar_actions')
    <button type="button" class="btn btn-primary btn-sm fw-semibold d-inline-flex align-items-center gap-1 px-3 py-2 rounded-3"
            data-bs-toggle="modal" data-bs-target="#createPoModal">
        <i class="bi bi-plus-lg"></i> Create Purchase Order
    </button>
@endsection

{{-- =======================================================================
     PART 2: Main Purchase Orders Content (Injected into layouts/admin.blade.php)
     ======================================================================= --}}
@section('content')

    {{-- SECTION A: PO Summary KPI Cards ($summary from PurchaseOrderController@index) --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-medium">Total Purchase Orders</div>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($summary['total_pos'] ?? 0) }}</h4>
                    <small class="text-muted">All PO records</small>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-medium">Open POs (Awaiting Delivery)</div>
                    <h4 class="fw-bold mb-0 text-warning">{{ number_format($summary['open_pos'] ?? 0) }}</h4>
                    <small class="text-muted">LKR {{ number_format($summary['total_open_value'] ?? 0, 2) }}</small>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-truck-flatbed"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-medium">Received POs</div>
                    <h4 class="fw-bold mb-0 text-success">{{ number_format($summary['received_pos'] ?? 0) }}</h4>
                    <small class="text-muted">Added to inventory stock</small>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-box-arrow-in-down"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-medium">Received Stock Value</div>
                    <h5 class="fw-bold mb-0 text-dark">LKR {{ number_format($summary['total_received_value'] ?? 0, 2) }}</h5>
                    <small class="text-success fw-medium">Total procured goods</small>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-cash-coin"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION B: Search & Filter Bar --}}
    <div class="content-card p-3 mb-4">
        <form action="{{ route('admin.purchase-orders.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label small fw-semibold text-secondary mb-1">Search PO # / Supplier / Product</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        class="form-control"
                        placeholder="Search by PO number, supplier company, product name, or SKU..."
                    >
                </div>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Supplier</label>
                <select name="supplier_id" class="form-select form-select-sm">
                    <option value="">All Suppliers</option>
                    @foreach ($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ (string) ($filters['supplier_id'] ?? '') === (string) $sup->id ? 'selected' : '' }}>
                            {{ $sup->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">PO Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="ordered" {{ ($filters['status'] ?? '') === 'ordered' ? 'selected' : '' }}>Ordered (Open)</option>
                    <option value="received" {{ ($filters['status'] ?? '') === 'received' ? 'selected' : '' }}>Received</option>
                    <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- SECTION C: Purchase Orders Data Table --}}
    <div class="content-card p-3 p-sm-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-receipt-cutoff text-primary me-1"></i> Purchase Orders Register ({{ $purchaseOrders->count() }} orders)
                </h6>
                <small class="text-muted">Click "Receive Stock" when goods arrive to automatically increase product inventory</small>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="text-nowrap">PO # / Date</th>
                        <th>Supplier</th>
                        <th>Product / SKU</th>
                        <th class="text-center text-nowrap">Ordered Qty</th>
                        <th class="text-end text-nowrap">Unit Cost</th>
                        <th class="text-end text-nowrap">Total (LKR)</th>
                        <th class="text-nowrap">Status</th>
                        <th class="text-end text-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchaseOrders as $po)
                        @php
                            $badge = $po->statusBadge();
                        @endphp
                        <tr>
                            <!-- PO Number & Expected Date -->
                            <td class="text-nowrap">
                                <div class="fw-bold text-primary"><code>{{ $po->po_number }}</code></div>
                                <small class="text-muted">
                                    Due: {{ optional($po->expected_date)->format('d M Y') ?? 'N/A' }}
                                </small>
                            </td>

                            <!-- Supplier -->
                            <td>
                                <div class="fw-semibold text-dark">{{ $po->supplier->company_name ?? 'N/A' }}</div>
                                <small class="text-muted"><code>{{ $po->supplier->supplier_code ?? '' }}</code></small>
                            </td>

                            <!-- Product -->
                            <td>
                                <div class="fw-semibold text-dark">{{ $po->product->name ?? 'N/A' }}</div>
                                <small class="text-muted">
                                    <code>{{ $po->product->sku ?? '' }}</code>
                                    &bull; Current Stock: <strong>{{ $po->product->stock_quantity ?? 0 }}</strong>
                                </small>
                            </td>

                            <!-- Ordered Quantity -->
                            <td class="text-center text-nowrap">
                                <span class="fw-bold fs-6 text-dark">{{ $po->quantity }}</span>
                                <small class="text-muted d-block">{{ $po->product->unit ?? 'pcs' }}</small>
                            </td>

                            <!-- Unit Cost -->
                            <td class="text-end text-secondary text-nowrap">
                                LKR {{ number_format((float) $po->unit_cost, 2) }}
                            </td>

                            <!-- Total Amount -->
                            <td class="text-end fw-bold text-dark text-nowrap">
                                LKR {{ number_format((float) $po->total_amount, 2) }}
                            </td>

                            <!-- Status Badge -->
                            <td class="text-nowrap">
                                <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                                @if ($po->status === 'received' && $po->receiver)
                                    <div class="text-muted" style="font-size: 0.7rem;">
                                        By {{ $po->receiver->name }}
                                    </div>
                                @endif
                            </td>

                            <!-- Actions (Uniform Bootstrap .btn-group.btn-group-sm with direct <button> children) -->
                            <td class="text-end text-nowrap">
                                @if ($po->status === 'ordered')
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button"
                                                class="btn btn-outline-success text-nowrap"
                                                title="Receive Goods & Add to Stock"
                                                onclick="document.getElementById('receive-po-form-{{ $po->id }}').submit();">
                                            <i class="bi bi-box-arrow-in-down me-1"></i>Receive
                                        </button>
                                        <button type="button"
                                                class="btn btn-outline-danger text-nowrap"
                                                title="Cancel PO"
                                                onclick="document.getElementById('cancel-po-form-{{ $po->id }}').submit();">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>

                                    <!-- Hidden Forms Outside .btn-group -->
                                    <form id="receive-po-form-{{ $po->id }}" action="{{ route('admin.purchase-orders.receive', $po) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                    <form id="cancel-po-form-{{ $po->id }}" action="{{ route('admin.purchase-orders.cancel', $po) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                @else
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button"
                                                class="btn btn-outline-danger text-nowrap"
                                                title="Delete Record"
                                                onclick="if(confirm('Delete this Purchase Order record?')) document.getElementById('delete-po-form-{{ $po->id }}').submit();">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <form id="delete-po-form-{{ $po->id }}" action="{{ route('admin.purchase-orders.destroy', $po) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt-cutoff fs-2 d-block mb-2"></i>
                                No purchase orders found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

{{-- =======================================================================
     PART 3: Create Purchase Order Modal (Injected into @stack('modals'))
     ======================================================================= --}}
@push('modals')
    <div class="modal fade" id="createPoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title fw-bold">
                        <i class="bi bi-receipt-cutoff me-2"></i>Create New Purchase Order (PO)
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.purchase-orders.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <!-- Supplier Selection -->
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Select Supplier <span class="text-danger">*</span></label>
                                <select name="supplier_id" class="form-select form-select-sm" required>
                                    @foreach ($suppliers as $sup)
                                        <option value="{{ $sup->id }}">
                                            {{ $sup->company_name }} ({{ $sup->supplier_code }} • {{ $sup->payment_terms }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Expected Delivery Date -->
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Expected Delivery Date</label>
                                <input type="date" name="expected_date" class="form-control form-control-sm" value="{{ now()->addDays(5)->toDateString() }}">
                            </div>

                            <!-- Product Selection -->
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Select Product to Order <span class="text-danger">*</span></label>
                                <select name="product_id" id="poProductSelect" class="form-select form-select-sm" required onchange="syncPoCostPrice()">
                                    @foreach ($products as $prod)
                                        <option value="{{ $prod->id }}" data-cost="{{ $prod->cost_price }}" data-stock="{{ $prod->stock_quantity }}">
                                            {{ $prod->name }} ({{ $prod->sku }}) — Current Stock: {{ $prod->stock_quantity }} {{ $prod->unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Order Quantity -->
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Order Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="poQtyInput" min="1" value="10" class="form-control form-control-sm" required oninput="calcPoTotal()">
                            </div>

                            <!-- Unit Cost Price -->
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Unit Cost Price (LKR) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="unit_cost" id="poCostInput" class="form-control form-control-sm" required oninput="calcPoTotal()">
                            </div>

                            <!-- Live Total PO Amount -->
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-secondary">Total PO Amount</label>
                                <div class="p-2 px-3 rounded-3 border bg-light d-flex justify-content-between align-items-center" style="height: 31px;">
                                    <span class="fw-bold text-primary small" id="poTotalPreview">LKR 0.00</span>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="col-12">
                                <label class="form-label small fw-semibold">PO Notes / Delivery Instructions</label>
                                <textarea name="notes" rows="2" class="form-control form-control-sm" placeholder="Optional delivery terms or batch notes..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-semibold px-4">
                            <i class="bi bi-send-check me-1"></i> Issue Purchase Order
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
    const poProductSelect = document.getElementById('poProductSelect');
    const poQtyInput = document.getElementById('poQtyInput');
    const poCostInput = document.getElementById('poCostInput');
    const poTotalPreview = document.getElementById('poTotalPreview');

    function syncPoCostPrice() {
        if (!poProductSelect || !poCostInput) return;
        const opt = poProductSelect.options[poProductSelect.selectedIndex];
        if (opt) {
            poCostInput.value = opt.getAttribute('data-cost') || '0.00';
            calcPoTotal();
        }
    }

    function calcPoTotal() {
        if (!poQtyInput || !poCostInput || !poTotalPreview) return;
        const qty = parseInt(poQtyInput.value, 10) || 0;
        const cost = parseFloat(poCostInput.value) || 0;
        const total = qty * cost;
        poTotalPreview.textContent = 'LKR ' + total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    document.addEventListener('DOMContentLoaded', syncPoCostPrice);
</script>
@endpush
