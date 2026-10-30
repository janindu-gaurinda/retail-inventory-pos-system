{{--
================================================================================
FILE PATH: resources/views/admin/stock_adjustments/index.blade.php
DESCRIPTION: Stock Adjustments Page UI (Module 5 of ISM System).
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
- Route (GET /admin/stock-adjustments): routes/web.php -> StockAdjustmentController@index ('admin.stock-adjustments.index')
- Route (POST /admin/stock-adjustments): routes/web.php -> StockAdjustmentController@store ('admin.stock-adjustments.store')
- Route (PATCH .../approve): routes/web.php -> StockAdjustmentController@approve ('admin.stock-adjustments.approve')
- Route (PATCH .../reject): routes/web.php -> StockAdjustmentController@reject ('admin.stock-adjustments.reject')
- Route (DELETE .../{stockAdjustment}): routes/web.php -> StockAdjustmentController@destroy ('admin.stock-adjustments.destroy')
- Backend Controller: app/Http/Controllers/Admin/StockAdjustmentController.php
================================================================================
--}}
@extends('layouts.admin')

@php
    $currentUser = auth()->user();
    $canCreate = $currentUser->canCreateStockAdjustment();
    $canApprove = $currentUser->canApproveStockAdjustment();
@endphp

{{-- =======================================================================
     PART 1: Dynamic Navbar Header Sections (Injected into partials/navbar.blade.php)
     ======================================================================= --}}
@section('title', 'Stock Adjustments | ISM System')
@section('page_title', 'Stock Adjustments')
@section('page_badge')
    @if ($canCreate && $canApprove)
        <span class="badge text-bg-primary bg-opacity-10 text-primary border border-primary-subtle">
            Full Access (Create &amp; Approve)
        </span>
    @elseif ($canApprove)
        <span class="badge text-bg-info bg-opacity-10 text-info border border-info-subtle">
            Approval Mode (Store Manager)
        </span>
    @else
        <span class="badge text-bg-warning bg-opacity-10 text-warning border border-warning-subtle">
            Create Mode (Stock Manager)
        </span>
    @endif
@endsection
@section('page_subtitle', 'Record stock recounts, damaged goods, and approve inventory quantity adjustments')

@section('topbar_actions')
    @if ($canCreate)
        <button type="button" class="btn btn-primary btn-sm fw-semibold d-inline-flex align-items-center gap-1 px-3 py-2 rounded-3"
                data-bs-toggle="modal" data-bs-target="#createAdjustmentModal">
            <i class="bi bi-plus-lg"></i> New Stock Adjustment
        </button>
    @endif
@endsection

{{-- =======================================================================
     PART 2: Main Stock Adjustments Content (Injected into layouts/admin.blade.php)
     ======================================================================= --}}
@section('content')

    {{-- SECTION A: Summary KPI Cards ($summary from StockAdjustmentController@index) --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-medium">Total Adjustments</div>
                    <h4 class="fw-bold mb-0 text-dark">{{ $summary['total_count'] ?? 0 }}</h4>
                    <small class="text-muted">All recorded requests</small>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-sliders"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-medium">Pending Approval</div>
                    <h4 class="fw-bold mb-0 text-warning">{{ $summary['pending_count'] ?? 0 }}</h4>
                    <small class="text-muted">Awaiting Admin / Store Manager</small>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-medium">Approved &amp; Applied</div>
                    <h4 class="fw-bold mb-0 text-success">{{ $summary['approved_count'] ?? 0 }}</h4>
                    <small class="text-muted">Stock quantities updated</small>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-medium">Rejected Requests</div>
                    <h4 class="fw-bold mb-0 text-danger">{{ $summary['rejected_count'] ?? 0 }}</h4>
                    <small class="text-muted">No stock change applied</small>
                </div>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION B: Search & Filter Bar --}}
    <div class="content-card p-3 mb-4">
        <form action="{{ route('admin.stock-adjustments.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label small fw-semibold text-secondary mb-1">Search Ref # / Product / Reason</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        class="form-control"
                        placeholder="Search by ADJ reference, product name, SKU, or reason..."
                    >
                </div>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Adjustment Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types (+ / -)</option>
                    <option value="addition" {{ ($filters['type'] ?? '') === 'addition' ? 'selected' : '' }}>Stock Addition (+)</option>
                    <option value="subtraction" {{ ($filters['type'] ?? '') === 'subtraction' ? 'selected' : '' }}>Stock Subtraction (-)</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="approved" {{ ($filters['status'] ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('admin.stock-adjustments.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- SECTION C: Stock Adjustments Table --}}
    <div class="content-card p-3 p-sm-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-clipboard2-pulse text-primary me-1"></i> Stock Adjustment Log ({{ $adjustments->count() }} records)
                </h6>
                <small class="text-muted">
                    Role Permissions: Admin (Create &amp; Approve) &bull; Store Manager (Approve) &bull; Stock Manager (Create)
                </small>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="text-nowrap">Ref # / Date</th>
                        <th>Product / SKU</th>
                        <th class="text-nowrap">Type &amp; Qty</th>
                        <th class="text-center text-nowrap">Stock Impact</th>
                        <th>Reason / Notes</th>
                        <th class="text-nowrap">Requested By</th>
                        <th class="text-nowrap">Status</th>
                        <th class="text-end text-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($adjustments as $adj)
                        @php
                            $badge = $adj->statusBadge();
                            $isAddition = $adj->adjustment_type === 'addition';
                        @endphp
                        <tr>
                            <!-- Ref # & Date -->
                            <td class="text-nowrap">
                                <div class="fw-bold text-primary"><code>{{ $adj->reference_no }}</code></div>
                                <small class="text-muted">{{ optional($adj->created_at)->format('d M Y, h:i A') }}</small>
                            </td>

                            <!-- Product -->
                            <td>
                                <div class="fw-semibold text-dark">{{ $adj->product->name ?? 'Deleted Product' }}</div>
                                <small class="text-muted">
                                    <code>{{ $adj->product->sku ?? 'N/A' }}</code>
                                    &bull; Current Stock: <strong>{{ $adj->product->stock_quantity ?? 0 }}</strong>
                                </small>
                            </td>

                            <!-- Type & Quantity -->
                            <td class="text-nowrap">
                                @if ($isAddition)
                                    <span class="badge text-bg-success bg-opacity-10 text-success border border-success-subtle fw-bold">
                                        <i class="bi bi-plus-circle me-1"></i>+{{ $adj->quantity }} {{ $adj->product->unit ?? 'pcs' }}
                                    </span>
                                @else
                                    <span class="badge text-bg-danger bg-opacity-10 text-danger border border-danger-subtle fw-bold">
                                        <i class="bi bi-dash-circle me-1"></i>-{{ $adj->quantity }} {{ $adj->product->unit ?? 'pcs' }}
                                    </span>
                                @endif
                            </td>

                            <!-- Stock Impact (Previous -> New) -->
                            <td class="text-center text-nowrap">
                                <span class="text-muted">{{ $adj->previous_stock }}</span>
                                <i class="bi bi-arrow-right mx-1 text-secondary"></i>
                                <span class="fw-bold text-dark">{{ $adj->new_stock }}</span>
                            </td>

                            <!-- Reason & Notes -->
                            <td>
                                <div class="fw-medium text-dark">{{ $adj->reason }}</div>
                                @if ($adj->notes)
                                    <small class="text-muted">{{ $adj->notes }}</small>
                                @endif
                            </td>

                            <!-- Requested By / Approved By -->
                            <td class="text-nowrap">
                                <div class="fw-medium">{{ $adj->requester->name ?? 'System' }}</div>
                                @if ($adj->approver)
                                    <small class="text-muted">
                                        By: {{ $adj->approver->name }}
                                    </small>
                                @endif
                            </td>

                            <!-- Status Badge -->
                            <td class="text-nowrap">
                                <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                            </td>

                            <!-- Action Buttons (Uniform Bootstrap .btn-group.btn-group-sm with direct <button> children) -->
                            <td class="text-end text-nowrap">
                                @if ($adj->status === 'pending' && $canApprove)
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button"
                                                class="btn btn-outline-success text-nowrap"
                                                title="Approve & Apply Stock Change"
                                                onclick="document.getElementById('approve-adj-form-{{ $adj->id }}').submit();">
                                            <i class="bi bi-check-lg me-1"></i>Approve
                                        </button>
                                        <button type="button"
                                                class="btn btn-outline-danger text-nowrap"
                                                title="Reject Request"
                                                onclick="document.getElementById('reject-adj-form-{{ $adj->id }}').submit();">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>

                                    <!-- Hidden Forms Outside .btn-group to Preserve Uniform Button Group Styling -->
                                    <form id="approve-adj-form-{{ $adj->id }}" action="{{ route('admin.stock-adjustments.approve', $adj) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                    <form id="reject-adj-form-{{ $adj->id }}" action="{{ route('admin.stock-adjustments.reject', $adj) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                @elseif ($canApprove)
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button"
                                                class="btn btn-outline-danger text-nowrap"
                                                title="Delete Log"
                                                onclick="if(confirm('Delete this adjustment record?')) document.getElementById('delete-adj-form-{{ $adj->id }}').submit();">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <form id="delete-adj-form-{{ $adj->id }}" action="{{ route('admin.stock-adjustments.destroy', $adj) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @else
                                    <span class="text-muted small">Awaiting Manager</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-sliders fs-2 d-block mb-2"></i>
                                No stock adjustment records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

{{-- =======================================================================
     PART 3: Create Stock Adjustment Modal (Injected into @stack('modals'))
     ======================================================================= --}}
@push('modals')
    @if ($canCreate)
        <div class="modal fade" id="createAdjustmentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-primary text-white">
                        <h6 class="modal-title fw-bold">
                            <i class="bi bi-sliders me-2"></i>Create Stock Adjustment
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('admin.stock-adjustments.store') }}" method="POST">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <!-- Product Selection -->
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Select Product <span class="text-danger">*</span></label>
                                    <select name="product_id" id="adjProductSelect" class="form-select form-select-sm" required onchange="updateStockPreview()">
                                        @foreach ($products as $prod)
                                            <option value="{{ $prod->id }}" data-stock="{{ $prod->stock_quantity }}" data-unit="{{ $prod->unit }}">
                                                {{ $prod->name }} ({{ $prod->sku }}) — Current Stock: {{ $prod->stock_quantity }} {{ $prod->unit }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Adjustment Type -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Adjustment Type <span class="text-danger">*</span></label>
                                    <select name="adjustment_type" id="adjTypeSelect" class="form-select form-select-sm" required onchange="updateStockPreview()">
                                        <option value="subtraction">Subtraction (- Reduce Stock)</option>
                                        <option value="addition">Addition (+ Add Stock)</option>
                                    </select>
                                </div>

                                <!-- Quantity -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Adjustment Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" id="adjQtyInput" min="1" value="1" class="form-control form-control-sm" required oninput="updateStockPreview()">
                                </div>

                                <!-- Live Stock Impact Preview Box -->
                                <div class="col-12">
                                    <div class="p-2 px-3 rounded-3 bg-light border d-flex justify-content-between align-items-center small">
                                        <span class="text-muted">Projected Stock Change:</span>
                                        <span class="fw-bold text-dark" id="liveStockImpactText">0 → 0</span>
                                    </div>
                                </div>

                                <!-- Reason -->
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Adjustment Reason <span class="text-danger">*</span></label>
                                    <select name="reason" class="form-select form-select-sm" required>
                                        <option value="Physical Audit Recount">Physical Audit Recount</option>
                                        <option value="Damaged / Defective Goods">Damaged / Defective Goods</option>
                                        <option value="Expired / Write-off">Expired / Write-off</option>
                                        <option value="Found Unrecorded Stock">Found Unrecorded Stock</option>
                                        <option value="Sample / Internal Use">Sample / Internal Use</option>
                                    </select>
                                </div>

                                <!-- Notes -->
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Additional Notes</label>
                                    <textarea name="notes" rows="2" class="form-control form-control-sm" placeholder="Optional explanation for manager approval..."></textarea>
                                </div>

                                @if ($canApprove)
                                    <!-- Admin Auto-Approve Option -->
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="auto_approve" value="1" id="autoApproveCheck" checked>
                                            <label class="form-check-label small fw-semibold text-success" for="autoApproveCheck">
                                                Immediately approve and update product stock now
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm fw-semibold px-4">
                                <i class="bi bi-check2-circle me-1"></i> Submit Adjustment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endpush

{{-- =======================================================================
     PART 4: Page-Specific Scripts (Injected into @stack('scripts'))
     ======================================================================= --}}
@push('scripts')
<script>
    function updateStockPreview() {
        const prodSelect = document.getElementById('adjProductSelect');
        const typeSelect = document.getElementById('adjTypeSelect');
        const qtyInput = document.getElementById('adjQtyInput');
        const previewEl = document.getElementById('liveStockImpactText');

        if (!prodSelect || !typeSelect || !qtyInput || !previewEl) return;

        const selectedOpt = prodSelect.options[prodSelect.selectedIndex];
        const currentStock = parseInt(selectedOpt.getAttribute('data-stock'), 10) || 0;
        const unit = selectedOpt.getAttribute('data-unit') || 'pcs';
        const qty = Math.max(1, parseInt(qtyInput.value, 10) || 0);
        const isAdd = typeSelect.value === 'addition';

        const newStock = isAdd ? (currentStock + qty) : Math.max(0, currentStock - qty);
        previewEl.textContent = `${currentStock} ${unit} → ${newStock} ${unit} (${isAdd ? '+' : '-'}${qty})`;
    }

    document.addEventListener('DOMContentLoaded', updateStockPreview);
</script>
@endpush
