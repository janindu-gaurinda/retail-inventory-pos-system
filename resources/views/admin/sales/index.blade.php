{{--
================================================================================
FILE PATH: resources/views/admin/sales/index.blade.php
DESCRIPTION: Sales History / Invoices Page UI (Module 9 of ISM System).
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
- Route (GET /admin/sales): routes/web.php -> SaleController@index ('admin.sales.index')
- Route (PATCH /admin/sales/{sale}/refund): routes/web.php -> SaleController@refund ('admin.sales.refund')
- Route (DELETE /admin/sales/{sale}): routes/web.php -> SaleController@destroy ('admin.sales.destroy')
- Backend Controller: app/Http/Controllers/Admin/SaleController.php
- Sale & SaleItem Models: app/Models/Sale.php & app/Models/SaleItem.php
================================================================================
--}}
@extends('layouts.admin')

@php
    $currentUser = auth()->user();
    $canViewAllSales = $currentUser->canViewAllSales();
@endphp

{{-- =======================================================================
     PART 1: Dynamic Navbar Header Sections (Injected into partials/navbar.blade.php)
     ======================================================================= --}}
@section('title', 'Sales History / Invoices | ISM System')
@section('page_title', 'Sales History / Invoices')
@section('page_badge')
    @if ($canViewAllSales)
        <span class="badge text-bg-primary bg-opacity-10 text-primary border border-primary-subtle">
            All Shifts &amp; Cashiers
        </span>
    @else
        <span class="badge text-bg-success bg-opacity-10 text-success border border-success-subtle">
            Own Shift Only ({{ $currentUser->name }})
        </span>
    @endif
@endsection
@section('page_subtitle', 'View completed POS invoices, reprint customer receipts, and process stock refunds')

@section('topbar_actions')
    <a href="{{ route('admin.pos.index') }}" class="btn btn-primary btn-sm fw-semibold d-inline-flex align-items-center gap-1 px-3 py-2 rounded-3">
        <i class="bi bi-cart-plus"></i> New POS Sale
    </a>
@endsection

{{-- =======================================================================
     PART 2: Main Sales History / Invoices Content (Injected into layouts/admin.blade.php)
     ======================================================================= --}}
@section('content')

    {{-- SECTION A: Sales Summary KPI Cards ($summary from SaleController@index) --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-medium">Total Invoices</div>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($summary['total_invoices'] ?? 0) }}</h4>
                    <small class="text-muted">{{ $canViewAllSales ? 'All store registers' : 'Your shift invoices' }}</small>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-journal-text"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-medium">Net Completed Sales</div>
                    <h5 class="fw-bold mb-0 text-success">LKR {{ number_format($summary['completed_revenue'] ?? 0, 2) }}</h5>
                    <small class="text-muted">Excluding refunded bills</small>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-medium">Total Units Sold</div>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($summary['total_items_sold'] ?? 0) }}</h4>
                    <small class="text-muted">Items dispatched via POS</small>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-bag-check"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-medium">Refunded Invoices</div>
                    <h4 class="fw-bold mb-0 text-danger">{{ number_format($summary['refunded_count'] ?? 0) }}</h4>
                    <small class="text-muted">LKR {{ number_format($summary['refunded_amount'] ?? 0, 2) }}</small>
                </div>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION B: Search & Filter Bar --}}
    <div class="content-card p-3 mb-4">
        <form action="{{ route('admin.sales.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-{{ $canViewAllSales ? '4' : '6' }}">
                <label class="form-label small fw-semibold text-secondary mb-1">Search Invoice # / Customer / Phone</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        class="form-control"
                        placeholder="Search by INV number, customer name, or phone..."
                    >
                </div>
            </div>

            @if ($canViewAllSales)
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold text-secondary mb-1">Cashier / Staff</label>
                    <select name="cashier_id" class="form-select form-select-sm">
                        <option value="">All Cashiers</option>
                        @foreach ($cashiers as $cashier)
                            <option value="{{ $cashier->id }}" {{ (string) ($filters['cashier_id'] ?? '') === (string) $cashier->id ? 'selected' : '' }}>
                                {{ $cashier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Payment Method</label>
                <select name="payment_method" class="form-select form-select-sm">
                    <option value="">All Methods</option>
                    <option value="Cash" {{ ($filters['payment_method'] ?? '') === 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Card" {{ ($filters['payment_method'] ?? '') === 'Card' ? 'selected' : '' }}>Card</option>
                    <option value="QR / Bank" {{ ($filters['payment_method'] ?? '') === 'QR / Bank' ? 'selected' : '' }}>QR / Bank</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="refunded" {{ ($filters['status'] ?? '') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('admin.sales.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- SECTION C: Sales History / Invoices Table --}}
    <div class="content-card p-3 p-sm-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-receipt text-primary me-1"></i> POS Invoices Register ({{ $sales->count() }} invoices)
                </h6>
                <small class="text-muted">
                    @if ($canViewAllSales)
                        Admin &amp; Store Manager View: Showing all shifts, with Refund &amp; Stock Restoration controls
                    @else
                        Cashier Shift View: Showing invoices processed under your account ({{ $currentUser->name }})
                    @endif
                </small>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Invoice # / Date</th>
                        <th>Customer</th>
                        <th>Cashier</th>
                        <th class="text-center">Items</th>
                        <th>Payment</th>
                        <th class="text-end">Discount</th>
                        <th class="text-end">Grand Total</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <!-- Invoice # & Timestamp -->
                            <td>
                                <div class="fw-bold text-primary"><code>{{ $sale->invoice_no }}</code></div>
                                <small class="text-muted">{{ optional($sale->created_at)->format('d M Y, h:i A') }}</small>
                            </td>

                            <!-- Customer -->
                            <td>
                                <div class="fw-semibold text-dark">{{ $sale->customer_name }}</div>
                                @if ($sale->customer_phone)
                                    <small class="text-muted">{{ $sale->customer_phone }}</small>
                                @endif
                            </td>

                            <!-- Cashier -->
                            <td>
                                <div class="fw-medium">{{ $sale->cashier->name ?? 'System' }}</div>
                                <small class="text-muted">{{ $sale->cashier ? $sale->cashier->roleLabel() : '' }}</small>
                            </td>

                            <!-- Items Count -->
                            <td class="text-center">
                                <span class="badge rounded-pill text-bg-light border">{{ $sale->items_count }} units</span>
                            </td>

                            <!-- Payment Method -->
                            <td>
                                <span class="badge text-bg-light border">{{ $sale->payment_method }}</span>
                            </td>

                            <!-- Discount -->
                            <td class="text-end text-muted">
                                LKR {{ number_format((float) $sale->discount_amount, 2) }}
                            </td>

                            <!-- Grand Total -->
                            <td class="text-end fw-bold text-dark">
                                LKR {{ number_format((float) $sale->grand_total, 2) }}
                            </td>

                            <!-- Status -->
                            <td>
                                @if ($sale->status === 'completed')
                                    <span class="badge text-bg-success">Completed</span>
                                @else
                                    <span class="badge text-bg-danger">Refunded</span>
                                @endif
                            </td>

                            <!-- Actions (Uniform Bootstrap .btn-group.btn-group-sm with direct <button> children) -->
                            <td class="text-end text-nowrap">
                                <div class="btn-group btn-group-sm" role="group">
                                    <!-- View & Print Receipt -->
                                    <button
                                        type="button"
                                        class="btn btn-outline-dark text-nowrap"
                                        title="View / Print Receipt"
                                        onclick='openInvoiceModal(@json($sale))'
                                    >
                                        <i class="bi bi-printer"></i>
                                    </button>

                                    @if ($canViewAllSales && $sale->status === 'completed')
                                        <!-- Refund & Restore Stock -->
                                        <button
                                            type="button"
                                            class="btn btn-outline-warning text-nowrap"
                                            title="Refund & Restore Stock"
                                            onclick="if(confirm('Refund invoice {{ $sale->invoice_no }} and restore item quantities to stock?')) document.getElementById('refund-sale-form-{{ $sale->id }}').submit();"
                                        >
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    @endif

                                    @if ($canViewAllSales)
                                        <!-- Delete Invoice -->
                                        <button
                                            type="button"
                                            class="btn btn-outline-danger text-nowrap"
                                            title="Delete Invoice"
                                            onclick="if(confirm('Permanently delete invoice {{ $sale->invoice_no }}?')) document.getElementById('delete-sale-form-{{ $sale->id }}').submit();"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>

                                @if ($canViewAllSales && $sale->status === 'completed')
                                    <form id="refund-sale-form-{{ $sale->id }}" action="{{ route('admin.sales.refund', $sale) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                @endif

                                @if ($canViewAllSales)
                                    <form id="delete-sale-form-{{ $sale->id }}" action="{{ route('admin.sales.destroy', $sale) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                                No sales invoices found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

{{-- =======================================================================
     PART 3: Printable Invoice / Receipt Modal (Injected into @stack('modals'))
     ======================================================================= --}}
@push('modals')
    <div class="modal fade" id="invoiceReceiptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white no-print">
                    <h6 class="modal-title fw-bold">
                        <i class="bi bi-receipt me-2"></i>Invoice Details &amp; Receipt
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="pos-receipt-box small">
                        <div class="text-center border-bottom pb-2 mb-3">
                            <div class="fw-bold fs-5 text-uppercase">{{ $systemSettings['store_name'] ?? 'ISM RETAIL STORE' }}</div>
                            <div class="text-muted" style="font-size: 0.74rem;">
                                {{ $systemSettings['store_address'] ?? 'Colombo, Sri Lanka' }}
                            </div>
                            <div class="text-muted" style="font-size: 0.74rem;">
                                Tel: {{ $systemSettings['store_phone'] ?? '+94 11 234 5678' }}
                                @if (!empty($systemSettings['store_email']))
                                    &bull; {{ $systemSettings['store_email'] }}
                                @endif
                            </div>
                            @if (!empty($systemSettings['tax_registration_no']))
                                <div class="text-muted" style="font-size: 0.72rem;">
                                    Tax / VAT Reg: {{ $systemSettings['tax_registration_no'] }}
                                </div>
                            @endif
                            <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                {{ $systemSettings['receipt_header_note'] ?? 'Official Sales Invoice / Receipt' }}
                            </div>
                            <div class="fw-bold text-primary mt-1" id="inv_no"></div>
                            <div class="text-muted" style="font-size: 0.72rem;" id="inv_date"></div>
                        </div>

                        <div class="row mb-2" style="font-size: 0.78rem;">
                            <div class="col-6">
                                <div><strong>Customer:</strong> <span id="inv_customer"></span></div>
                                <div><strong>Phone:</strong> <span id="inv_phone"></span></div>
                            </div>
                            <div class="col-6 text-end">
                                <div><strong>Cashier:</strong> <span id="inv_cashier"></span></div>
                                <div><strong>Payment:</strong> <span id="inv_method"></span></div>
                            </div>
                        </div>

                        <table class="table table-sm table-bordered my-2" style="font-size: 0.76rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Item / SKU</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody id="inv_items_tbody"></tbody>
                        </table>

                        <div style="font-size: 0.8rem;">
                            <div class="d-flex justify-content-between">
                                <span>Subtotal:</span>
                                <span id="inv_subtotal"></span>
                            </div>
                            <div class="d-flex justify-content-between text-danger">
                                <span>Discount:</span>
                                <span id="inv_discount"></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Tax:</span>
                                <span id="inv_tax"></span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold border-top pt-1 mt-1 fs-6">
                                <span>GRAND TOTAL:</span>
                                <span id="inv_total"></span>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <span>Amount Paid:</span>
                                <span id="inv_paid"></span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Change Returned:</span>
                                <span id="inv_change"></span>
                            </div>
                        </div>

                        <div class="text-center border-top pt-2 mt-3 text-muted" style="font-size: 0.72rem;">
                            {{ $systemSettings['receipt_footer_note'] ?? 'Thank you for shopping with us!' }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-between no-print">
                    <span class="badge text-bg-secondary" id="inv_status_badge"></span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary btn-sm fw-semibold" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Print Invoice
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endpush

{{-- =======================================================================
     PART 4: Page-Specific Scripts (Injected into @stack('scripts'))
     ======================================================================= --}}
@push('scripts')
<script>
    function openInvoiceModal(sale) {
        document.getElementById('inv_no').textContent = sale.invoice_no;
        document.getElementById('inv_date').textContent = new Date(sale.created_at).toLocaleString();
        document.getElementById('inv_customer').textContent = sale.customer_name;
        document.getElementById('inv_phone').textContent = sale.customer_phone || 'N/A';
        document.getElementById('inv_cashier').textContent = sale.cashier ? sale.cashier.name : 'System';
        document.getElementById('inv_method').textContent = sale.payment_method;

        let rows = '';
        if (sale.items && sale.items.length) {
            sale.items.forEach(item => {
                rows += `
                    <tr>
                        <td>${item.product_name} <br><small class="text-muted">${item.sku}</small></td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-end">${Number(item.unit_price).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                        <td class="text-end fw-semibold">${Number(item.line_total).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                    </tr>
                `;
            });
        }
        document.getElementById('inv_items_tbody').innerHTML = rows;

        document.getElementById('inv_subtotal').textContent = 'LKR ' + Number(sale.subtotal).toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('inv_discount').textContent = '- LKR ' + Number(sale.discount_amount).toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('inv_tax').textContent = 'LKR ' + Number(sale.tax_amount).toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('inv_total').textContent = 'LKR ' + Number(sale.grand_total).toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('inv_paid').textContent = 'LKR ' + Number(sale.paid_amount).toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('inv_change').textContent = 'LKR ' + Number(sale.change_amount).toLocaleString(undefined, {minimumFractionDigits: 2});

        const badge = document.getElementById('inv_status_badge');
        badge.textContent = sale.status.toUpperCase();
        badge.className = 'badge ' + (sale.status === 'completed' ? 'text-bg-success' : 'text-bg-danger');

        new bootstrap.Modal(document.getElementById('invoiceReceiptModal')).show();
    }
</script>
@endpush
