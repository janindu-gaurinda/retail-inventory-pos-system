{{--
================================================================================
FILE PATH: resources/views/admin/reports/index.blade.php
DESCRIPTION: Reports & Analytics Page UI (Module 10 of ISM System).
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
- Route (GET /admin/reports): routes/web.php -> ReportController@index ('admin.reports.index')
- Backend Controller: app/Http/Controllers/Admin/ReportController.php
================================================================================
--}}
@extends('layouts.admin')

{{-- =======================================================================
     PART 1: Dynamic Navbar Header Sections (Injected into partials/navbar.blade.php)
     ======================================================================= --}}
@section('title', 'Reports & Analytics | ISM System')
@section('page_title', 'Reports & Analytics')
@section('page_badge')
    <span class="badge text-bg-primary bg-opacity-10 text-primary border border-primary-subtle">
        Sales, Profit &amp; Inventory Valuation
    </span>
@endsection
@section('page_subtitle', 'Financial performance, top-selling products, category stock valuation, and procurement summary')

@section('topbar_actions')
    <button type="button" class="btn btn-dark btn-sm fw-semibold d-inline-flex align-items-center gap-1 px-3 py-2 rounded-3 no-print"
            onclick="window.print()">
        <i class="bi bi-printer"></i> Print Report
    </button>
@endsection

{{-- =======================================================================
     PART 2: Main Reports & Analytics Content (Injected into layouts/admin.blade.php)
     ======================================================================= --}}
@section('content')

    {{-- Official Store Profile Header Strip (Visible on Screen & Printed Reports) --}}
    <div class="content-card p-3 p-sm-4 mb-4 border-start border-4 border-primary">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h5 class="fw-bold text-dark mb-0">{{ $systemSettings['store_name'] ?? 'ISM Retail Store' }}</h5>
                    <span class="badge bg-primary-subtle text-primary">{{ $systemSettings['store_code'] ?? 'BR-CMB-01' }}</span>
                </div>
                <div class="text-secondary small mt-1">
                    <i class="bi bi-geo-alt me-1"></i>{{ $systemSettings['store_address'] ?? 'Colombo, Sri Lanka' }}
                    &bull; <i class="bi bi-telephone ms-1 me-1"></i>{{ $systemSettings['store_phone'] ?? '+94 11 234 5678' }}
                    &bull; <i class="bi bi-envelope ms-1 me-1"></i>{{ $systemSettings['store_email'] ?? 'support@ism.com' }}
                </div>
            </div>
            <div class="text-md-end small">
                @if (!empty($systemSettings['tax_registration_no']))
                    <div class="fw-semibold text-dark">Tax / VAT Reg: <code>{{ $systemSettings['tax_registration_no'] }}</code></div>
                @endif
                <div class="text-muted">Report Generated: {{ now()->format($systemSettings['date_format'] ?? 'd M Y') }} {{ now()->format('H:i') }}</div>
            </div>
        </div>
    </div>

    {{-- ===================================================================
         SECTION A: Financial & Sales Performance KPIs ($salesKpi)
         =================================================================== --}}
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="fw-bold text-dark mb-0">
            <i class="bi bi-graph-up-arrow text-success me-1"></i> Sales Revenue &amp; Profit Margin Overview
        </h6>
        <small class="text-muted">Calculated from completed POS invoices</small>
    </div>

    <div class="row g-3 mb-4">
        <!-- KPI 1: Gross Sales Revenue -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-medium">Net Sales Revenue</div>
                    <h4 class="fw-bold mb-0 text-dark">LKR {{ number_format($salesKpi['revenue'] ?? 0, 2) }}</h4>
                    <small class="text-muted">{{ $salesKpi['invoices_count'] ?? 0 }} Invoices &bull; {{ $salesKpi['units_sold'] ?? 0 }} Units</small>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-currency-dollar"></i>
                </div>
            </div>
        </div>

        <!-- KPI 2: Estimated COGS (Cost of Goods Sold) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-medium">Cost of Goods Sold (COGS)</div>
                    <h4 class="fw-bold mb-0 text-secondary">LKR {{ number_format($salesKpi['cogs'] ?? 0, 2) }}</h4>
                    <small class="text-muted">Discounts Given: LKR {{ number_format($salesKpi['discounts'] ?? 0, 2) }}</small>
                </div>
                <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
        </div>

        <!-- KPI 3: Gross Profit & Margin -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-medium">Estimated Gross Profit</div>
                    <h4 class="fw-bold mb-0 text-success">LKR {{ number_format($salesKpi['gross_profit'] ?? 0, 2) }}</h4>
                    <span class="badge text-bg-success bg-opacity-10 text-success">
                        Margin: {{ $salesKpi['profit_margin'] ?? 0 }}%
                    </span>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-trophy-fill"></i>
                </div>
            </div>
        </div>

        <!-- KPI 4: Inventory Retail vs Cost Valuation -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card p-3 h-100 d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-secondary small fw-medium">On-Hand Stock Cost Value</div>
                    <h4 class="fw-bold mb-0 text-dark">LKR {{ number_format($inventoryReport['total_cost_valuation'] ?? 0, 0) }}</h4>
                    <small class="text-primary fw-semibold">
                        Retail Value: LKR {{ number_format($inventoryReport['total_retail_valuation'] ?? 0, 0) }}
                    </small>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-safe2-fill"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================================
         SECTION B: Top-Selling Products & Payment Method Breakdown
         =================================================================== --}}
    <div class="row g-4 mb-4">

        <!-- Left Column: Top-Selling Products Table -->
        <div class="col-12 col-xl-7">
            <div class="content-card p-3 p-sm-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-star-fill text-warning me-1"></i> Top-Selling Products by Revenue
                        </h6>
                        <small class="text-muted">Aggregated from completed POS line items</small>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Product / SKU</th>
                                <th class="text-center">Units Sold</th>
                                <th class="text-end">Total Revenue (LKR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topProducts as $prod)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $prod['product_name'] }}</div>
                                        <small class="text-muted"><code>{{ $prod['sku'] }}</code></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill text-bg-primary px-3">{{ $prod['qty_sold'] }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-dark">
                                        LKR {{ number_format($prod['revenue'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No sales recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Payment Method Breakdown -->
        <div class="col-12 col-xl-5">
            <div class="content-card p-3 p-sm-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-credit-card-2-front text-primary me-1"></i> Sales by Payment Method
                        </h6>
                        <small class="text-muted">Cash, Card &amp; QR / Bank distribution</small>
                    </div>
                </div>

                <div class="d-grid gap-3">
                    @foreach ($paymentBreakdown as $pm)
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark">
                                    {{ $pm['method'] }}
                                    <small class="text-muted fw-normal">({{ $pm['count'] }} invoices)</small>
                                </span>
                                <span class="fw-bold text-primary">LKR {{ number_format($pm['amount'], 2) }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $pm['share'] }}%;"></div>
                            </div>
                            <div class="text-end text-muted mt-1" style="font-size: 0.72rem;">
                                {{ $pm['share'] }}% of total sales
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- ===================================================================
         SECTION C: Category-Wise Inventory Valuation & Operations Summary
         =================================================================== --}}
    <div class="row g-4">

        <!-- Left Column: Category-Wise Stock Valuation Table -->
        <div class="col-12 col-xl-8">
            <div class="content-card p-3 p-sm-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-pie-chart-fill text-primary me-1"></i> Category-Wise Stock Valuation Report
                        </h6>
                        <small class="text-muted">Compares Cost Valuation vs Retail Valuation and Projected Profit per Category</small>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Category</th>
                                <th class="text-center">Products</th>
                                <th class="text-center">Total Stock Qty</th>
                                <th class="text-end">Cost Valuation</th>
                                <th class="text-end">Retail Valuation</th>
                                <th class="text-end">Expected Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categoryBreakdown as $cat)
                                <tr>
                                    <td class="fw-semibold text-dark">{{ $cat['category'] }}</td>
                                    <td class="text-center">{{ $cat['products_count'] }}</td>
                                    <td class="text-center fw-bold">{{ $cat['units_in_stock'] }}</td>
                                    <td class="text-end text-secondary">LKR {{ number_format($cat['cost_value'], 2) }}</td>
                                    <td class="text-end fw-semibold text-dark">LKR {{ number_format($cat['retail_value'], 2) }}</td>
                                    <td class="text-end fw-bold text-success">LKR {{ number_format($cat['expected_profit'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td>TOTAL INVENTORY</td>
                                <td class="text-center">{{ $inventoryReport['total_skus'] }}</td>
                                <td class="text-center">{{ $inventoryReport['total_units_in_stock'] }}</td>
                                <td class="text-end">LKR {{ number_format($inventoryReport['total_cost_valuation'], 2) }}</td>
                                <td class="text-end">LKR {{ number_format($inventoryReport['total_retail_valuation'], 2) }}</td>
                                <td class="text-end text-success">LKR {{ number_format($inventoryReport['projected_stock_profit'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Procurement (POs), Payables & Stock Adjustments Summary -->
        <div class="col-12 col-xl-4">
            <div class="content-card p-3 p-sm-4 h-100">
                <h6 class="fw-bold mb-3 text-dark">
                    <i class="bi bi-clipboard-data text-primary me-1"></i> Procurement &amp; Stock Audit Summary
                </h6>

                <table class="table table-sm table-bordered small mb-0">
                    <tbody>
                        <tr>
                            <th class="bg-light">Open Purchase Orders</th>
                            <td class="text-end fw-semibold">
                                {{ $operationsSummary['open_pos_count'] }} POs
                                <div class="text-muted" style="font-size: 0.72rem;">LKR {{ number_format($operationsSummary['open_pos_value'], 2) }}</div>
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">Received Purchase Orders</th>
                            <td class="text-end fw-semibold text-success">
                                {{ $operationsSummary['received_pos_count'] }} POs
                                <div class="text-muted" style="font-size: 0.72rem;">LKR {{ number_format($operationsSummary['received_pos_value'], 2) }}</div>
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">Total Supplier Payables</th>
                            <td class="text-end fw-bold text-danger">
                                LKR {{ number_format($operationsSummary['total_supplier_payables'], 2) }}
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">Low / Out of Stock SKUs</th>
                            <td class="text-end">
                                <span class="badge text-bg-warning">{{ $inventoryReport['low_stock_count'] }} Low</span>
                                <span class="badge text-bg-danger">{{ $inventoryReport['out_of_stock_count'] }} Out</span>
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">Approved Stock Additions</th>
                            <td class="text-end fw-bold text-success">+{{ $operationsSummary['approved_additions'] }} units</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Approved Stock Write-offs</th>
                            <td class="text-end fw-bold text-danger">-{{ $operationsSummary['approved_subtractions'] }} units</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Pending Adjustments</th>
                            <td class="text-end">
                                <span class="badge text-bg-warning">{{ $operationsSummary['pending_adjustments'] }} Pending</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection
