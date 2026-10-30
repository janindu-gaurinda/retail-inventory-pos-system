{{--
================================================================================
FILE PATH: resources/views/admin/dashboard.blade.php
DESCRIPTION: Admin Dashboard Page — Full (All Stats).
             Extends the shared Master Layout so Sidebar, Navbar, Footer, CSS,
             and JS are loaded dynamically from one common source.
================================================================================

SHARED LAYOUT & PARTIALS USED:
- Master Layout: resources/views/layouts/admin.blade.php
- Sidebar Partial: resources/views/partials/sidebar.blade.php
- Navbar Partial: resources/views/partials/navbar.blade.php
- Footer Partial: resources/views/partials/footer.blade.php
- Shared CSS / JS: public/assets/css/ism-admin.css & public/assets/js/ism-admin.js

CONNECTED BACKEND & ROUTE FILES:
- Route (GET /admin/dashboard): routes/web.php -> DashboardController@index (name: 'admin.dashboard')
- Backend Controller: app/Http/Controllers/Admin/DashboardController.php
================================================================================
--}}
@extends('layouts.admin')

{{-- =======================================================================
     PART 1: Page Header Configuration (Injected into partials/navbar.blade.php)
     ======================================================================= --}}
@section('title', 'Admin Dashboard (All Stats) | ISM System')
@section('page_title', 'Admin Dashboard')
@section('page_badge')
    <span class="badge text-bg-primary bg-opacity-10 text-primary border border-primary-subtle">
        Full (All Stats)
    </span>
@endsection
@section('page_subtitle', 'Complete overview of Sales, POS, Inventory, Suppliers, Purchase Orders & Users')

@section('topbar_actions')
    <a href="{{ route('admin.pos.index') }}" class="btn btn-primary btn-sm fw-semibold d-none d-md-inline-flex align-items-center gap-1 px-3 py-2 rounded-3">
        <i class="bi bi-cart-plus"></i> Open POS Checkout
    </a>
@endsection

{{-- =======================================================================
     PART 2: Main Dashboard Content (Injected into layouts/admin.blade.php)
     ======================================================================= --}}
@section('content')

    {{-- SECTION A: Quick Action Bar (All Admin Capabilities) --}}
    <div class="content-card p-3 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="small fw-bold text-uppercase text-secondary" style="letter-spacing: 0.05em;">
                <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Admin Quick Actions
            </span>
            <span class="small text-muted d-none d-md-inline">Full Access Enabled (All 12 Modules)</span>
        </div>
        <div class="row g-2">
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('admin.products.create') }}" class="quick-action-btn">
                    <i class="bi bi-plus-circle-fill text-primary fs-5"></i>
                    <span>Add Product</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('admin.products.barcodes') }}" class="quick-action-btn">
                    <i class="bi bi-upc-scan text-indigo fs-5" style="color: #4f46e5;"></i>
                    <span>Print Barcodes</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('admin.stock-adjustments.index') }}" class="quick-action-btn">
                    <i class="bi bi-sliders text-warning fs-5"></i>
                    <span>Approve Stock</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('admin.purchase-orders.index') }}" class="quick-action-btn">
                    <i class="bi bi-file-earmark-plus-fill text-info fs-5"></i>
                    <span>Create PO</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route('admin.pos.index') }}" class="quick-action-btn">
                    <i class="bi bi-display text-success fs-5"></i>
                    <span>POS Terminal</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a href="#users-section" class="quick-action-btn">
                    <i class="bi bi-person-plus-fill text-danger fs-5"></i>
                    <span>Manage Users</span>
                </a>
            </div>
        </div>
    </div>

    {{-- SECTION B: Sales & POS Statistics Row ($salesStats from DashboardController) --}}
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="fw-bold text-dark mb-0">
            <i class="bi bi-cash-coin text-success me-1"></i> Sales &amp; POS Statistics
        </h6>
        <small class="text-muted">Live Shift &amp; Monthly Revenue</small>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-medium mb-1">Today's POS Sales</div>
                        <h4 class="fw-bold mb-1 text-dark">LKR {{ number_format($salesStats['today_sales'] ?? 0, 2) }}</h4>
                        <span class="badge text-bg-success bg-opacity-10 text-success fw-semibold">
                            <i class="bi bi-graph-up-arrow me-1"></i> {{ $salesStats['today_growth'] ?? '+0%' }} vs yesterday
                        </span>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-medium mb-1">Monthly Revenue</div>
                        <h4 class="fw-bold mb-1 text-dark">LKR {{ number_format($salesStats['monthly_revenue'] ?? 0, 2) }}</h4>
                        <span class="badge text-bg-primary bg-opacity-10 text-primary fw-semibold">
                            <i class="bi bi-arrow-up-right me-1"></i> {{ $salesStats['monthly_growth'] ?? '+0%' }} this month
                        </span>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-wallet2"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-medium mb-1">Today's Invoices</div>
                        <h4 class="fw-bold mb-1 text-dark">{{ $salesStats['today_invoices'] ?? 0 }}</h4>
                        <small class="text-muted">Avg Basket: LKR {{ number_format($salesStats['avg_basket_value'] ?? 0, 0) }}</small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-medium mb-1">Active Users &amp; Roles</div>
                        <h4 class="fw-bold mb-1 text-dark">{{ $totalUsers ?? 4 }} Staff</h4>
                        <small class="text-success fw-medium">
                            <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>
                            {{ $salesStats['active_pos_registers'] ?? 1 }} POS Registers Active
                        </small>
                    </div>
                    <div class="stat-icon bg-dark bg-opacity-10 text-dark">
                        <i class="bi bi-person-badge"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION C: Inventory, Cost Valuation & Purchasing Stats Row ($inventoryStats) --}}
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="fw-bold text-dark mb-0">
            <i class="bi bi-boxes text-primary me-1"></i> Inventory, Stock Valuation &amp; Purchasing Stats
        </h6>
        <small class="text-muted">Includes Cost Price Valuation (Visible to Admin/Manager)</small>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-medium mb-1">Total Products</div>
                        <h4 class="fw-bold mb-1 text-dark">{{ number_format($inventoryStats['total_products'] ?? 0) }}</h4>
                        <small class="text-muted">Across {{ $inventoryStats['total_categories'] ?? 0 }} Categories</small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-medium mb-1">Inventory Cost Value</div>
                        <h4 class="fw-bold mb-1 text-dark">LKR {{ number_format($inventoryStats['inventory_cost_value'] ?? 0, 0) }}</h4>
                        <small class="text-success fw-medium">
                            Retail: LKR {{ number_format($inventoryStats['inventory_retail_value'] ?? 0, 0) }}
                        </small>
                    </div>
                    <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                        <i class="bi bi-safe2-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 border-danger border-opacity-25">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-medium mb-1">Low / Out of Stock</div>
                        <h4 class="fw-bold mb-1 text-danger">{{ $inventoryStats['low_stock_count'] ?? 0 }} Items</h4>
                        <span class="badge text-bg-danger">
                            {{ $inventoryStats['out_of_stock_count'] ?? 0 }} Out of Stock
                        </span>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-medium mb-1">Open POs &amp; Adjustments</div>
                        <h4 class="fw-bold mb-1 text-dark">{{ $inventoryStats['open_purchase_orders'] ?? 0 }} Open POs</h4>
                        <span class="badge text-bg-warning">
                            {{ $inventoryStats['pending_adjustments'] ?? 0 }} Adjustments to Approve
                        </span>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-clipboard2-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION D: Recent POS Sales & Low Stock Products Tables --}}
    <div class="row g-3 mb-4" id="sales-section">
        <div class="col-12 col-xl-6">
            <div class="content-card p-3 p-sm-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-receipt text-primary me-1"></i> Recent POS Sales / Invoices
                        </h6>
                        <small class="text-muted">All shifts &amp; cashiers (Admin Full View)</small>
                    </div>
                    <span class="badge text-bg-light border">All Shifts</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Cashier</th>
                                <th>Payment</th>
                                <th class="text-end">Total (LKR)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentSales as $sale)
                                <tr>
                                    <td class="fw-semibold text-primary">{{ $sale['invoice_no'] }}</td>
                                    <td>
                                        <div>{{ $sale['cashier'] }}</div>
                                        <small class="text-muted">{{ $sale['time'] }}</small>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-light border">{{ $sale['payment_method'] }}</span>
                                    </td>
                                    <td class="text-end fw-semibold">{{ number_format($sale['total'], 2) }}</td>
                                    <td>
                                        @if ($sale['status'] === 'Completed')
                                            <span class="badge text-bg-success">Completed</span>
                                        @else
                                            <span class="badge text-bg-secondary">{{ $sale['status'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No POS sales recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6" id="products-section">
            <div class="content-card p-3 p-sm-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-exclamation-octagon text-danger me-1"></i> Low Stock Products (With Cost Price)
                        </h6>
                        <small class="text-muted">Admin &amp; Managers view Cost Price; hidden for Cashiers</small>
                    </div>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-primary py-0 px-2">View All</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>SKU / Product</th>
                                <th class="text-center">Stock</th>
                                <th class="text-end">Cost Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lowStockItems as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $item['name'] }}</div>
                                        <small class="text-muted"><code>{{ $item['sku'] }}</code> &bull; {{ $item['category'] }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold {{ $item['stock'] === 0 ? 'text-danger' : 'text-warning' }}">
                                            {{ $item['stock'] }}
                                        </span>
                                        <small class="text-muted">/ {{ $item['min_level'] }}</small>
                                    </td>
                                    <td class="text-end fw-medium">
                                        LKR {{ number_format($item['cost_price'], 2) }}
                                    </td>
                                    <td>
                                        @if ($item['status'] === 'Out of Stock')
                                            <span class="badge text-bg-danger">Out of Stock</span>
                                        @elseif ($item['status'] === 'Critical')
                                            <span class="badge text-bg-warning">Critical</span>
                                        @else
                                            <span class="badge text-bg-info">Low Stock</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        No low-stock products right now.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION E: Pending Stock Adjustments & System Users Table --}}
    <div class="row g-3 mb-4" id="approvals-section">
        <div class="col-12 col-xl-6">
            <div class="content-card p-3 p-sm-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-sliders text-warning me-1"></i> Pending Stock Adjustments &amp; POs
                        </h6>
                        <small class="text-muted">Admin &amp; Store Manager can approve adjustments</small>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Ref #</th>
                                <th>Type / Details</th>
                                <th>By</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingApprovals as $req)
                                <tr>
                                    <td class="fw-semibold"><code>{{ $req['ref_no'] }}</code></td>
                                    <td>
                                        <div class="fw-medium text-dark">{{ $req['type'] }}</div>
                                        <small class="text-muted">{{ $req['details'] }}</small>
                                    </td>
                                    <td>{{ $req['requested_by'] }}</td>
                                    <td class="text-end">
                                        <a href="{{ $req['type'] === 'Stock Adjustment' ? route('admin.stock-adjustments.index') : route('admin.purchase-orders.index') }}"
                                           class="btn btn-sm btn-outline-primary py-0 px-2">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        No pending stock adjustments or open purchase orders.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6" id="users-section">
            <div class="content-card p-3 p-sm-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-people-fill text-primary me-1"></i> User Management (All 4 System Roles)
                        </h6>
                        <small class="text-muted">Loaded from <code>users</code> table via <code>app/Models/User.php</code></small>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary py-0 px-2">Manage Users</a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Dashboard Scope</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td class="fw-semibold">{{ $user->name }}</td>
                                    <td><code>{{ $user->email }}</code></td>
                                    <td>
                                        <span class="badge text-bg-primary">{{ $user->roleLabel() }}</span>
                                    </td>
                                    <td class="text-muted">
                                        @if (in_array($user->role, ['admin', 'super_admin', 'store_manager']))
                                            Full (All Stats)
                                        @elseif ($user->role === 'stock_manager')
                                            Inventory Stats Only
                                        @else
                                            Sales Stats Only
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION F: ISM Role-Based Access Matrix --}}
    <div class="content-card p-3 p-sm-4" id="matrix-section">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-shield-lock-fill text-primary me-1"></i> ISM Role-Based Access Matrix
                </h6>
                <small class="text-muted">System-wide module permissions configured across all 4 roles</small>
            </div>
            <span class="badge text-bg-success">Active Role: {{ auth()->user()->roleLabel() }} — Full Access</span>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Module / Page</th>
                        <th class="text-center bg-primary bg-opacity-10 text-primary">Admin (Current)</th>
                        <th class="text-center">Store Manager</th>
                        <th class="text-center">Stock Manager</th>
                        <th class="text-center">Cashier</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-semibold">Dashboard</td>
                        <td class="text-center bg-primary bg-opacity-10 fw-semibold text-primary">Full (All Stats)</td>
                        <td class="text-center">Full (All Stats)</td>
                        <td class="text-center">Inventory Stats Only</td>
                        <td class="text-center">Sales Stats Only</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Products List</td>
                        <td class="text-center bg-primary bg-opacity-10 fw-semibold text-primary">View &amp; Edit</td>
                        <td class="text-center">View &amp; Edit</td>
                        <td class="text-center">View &amp; Edit</td>
                        <td class="text-center text-muted">View Only (No Cost Price)</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Add / Edit Product</td>
                        <td class="text-center bg-primary bg-opacity-10 text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Barcode / Label Print</td>
                        <td class="text-center bg-primary bg-opacity-10 text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Stock Adjustments</td>
                        <td class="text-center bg-primary bg-opacity-10 text-success fw-semibold"><i class="bi bi-check-circle-fill"></i> Full</td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i> (Approve)</td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i> (Create)</td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Suppliers Directory</td>
                        <td class="text-center bg-primary bg-opacity-10 text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Purchase Orders (POs)</td>
                        <td class="text-center bg-primary bg-opacity-10 text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i> (Create &amp; Receive)</td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">POS Checkout Screen</td>
                        <td class="text-center bg-primary bg-opacity-10 text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Sales History / Invoices</td>
                        <td class="text-center bg-primary bg-opacity-10 text-success"><i class="bi bi-check-circle-fill"></i> All</td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i> All</td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i> (Own Shift Only)</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Reports &amp; Analytics</td>
                        <td class="text-center bg-primary bg-opacity-10 text-success"><i class="bi bi-check-circle-fill"></i> All</td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i> (Sales/Stock)</td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">User Management</td>
                        <td class="text-center bg-primary bg-opacity-10 text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">System Settings</td>
                        <td class="text-center bg-primary bg-opacity-10 text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

@endsection
