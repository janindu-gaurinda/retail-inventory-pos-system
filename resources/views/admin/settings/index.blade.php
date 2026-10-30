{{--
================================================================================
FILE PATH: resources/views/admin/settings/index.blade.php
DESCRIPTION: Module 12 — System Settings Page (Admin Only).
             Configures Store Profile, Currency & VAT/Tax Rate, POS Thermal
             Receipt Layout (with Live 80mm Preview), Inventory Alert Rules,
             Role Security Policies, and System Environment Diagnostics.
================================================================================

EXTENDS MASTER LAYOUT:
- Shared Master Layout: resources/views/layouts/admin.blade.php
  (Automatically injects Shared Sidebar, Shared Top Navbar, Shared Footer,
   Global CSS public/assets/css/ism-admin.css, and Global JS public/assets/js/ism-admin.js)

CONNECTED BACKEND FILES:
- Controller: app/Http/Controllers/Admin/SettingController.php
  - index()  -> GET  /admin/settings        (route: 'admin.settings.index')
  - update() -> PUT  /admin/settings        (route: 'admin.settings.update')
  - reset()  -> POST /admin/settings/reset  (route: 'admin.settings.reset')
- Model: app/Models/SystemSetting.php
- Migration: database/migrations/2026_10_29_000006_create_system_settings_table.php
- Routes File: routes/web.php
================================================================================
--}}

@extends('layouts.admin')

{{-- PART 1: Page Metadata & Dynamic Top Navbar Header --}}
@section('title', 'System Settings — ISM System')
@section('page_title', 'System Settings & Configuration')
@section('page_badge', 'Module 12 · Admin Only')
@section('page_subtitle', 'Configure store profile, currency, tax/VAT rate, thermal POS receipt layout, inventory thresholds, and role security policies.')

{{-- PART 2: Top Navbar Action Buttons --}}
@section('topbar_actions')
    <form action="{{ route('admin.settings.reset') }}" method="POST"
          onsubmit="return confirm('Restore all System Settings to factory default values?');">
        @csrf
        <button type="submit" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
            <i class="bi bi-arrow-counterclockwise"></i>
            <span class="d-none d-sm-inline">Reset Defaults</span>
        </button>
    </form>
    <button type="submit" form="systemSettingsForm" class="btn btn-primary btn-sm d-flex align-items-center gap-1 fw-semibold px-3">
        <i class="bi bi-check2-circle"></i>
        <span>Save All Settings</span>
    </button>
@endsection

{{-- PART 3: Main Page Content --}}
@section('content')

    {{-- Flash Alerts & Validation Errors --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please fix the following configuration errors:</div>
            <ul class="mb-0 small">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ====================================================================
         PART 4: Quick Configuration Summary Strip (4 KPI Cards)
         ==================================================================== --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card kpi-card h-100 p-3 border-start border-4 border-primary">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.72rem;">Active Store Branch</div>
                        <div class="fw-bold text-dark mt-1 text-truncate" style="max-width: 180px;">{{ $settings['store_code'] }}</div>
                        <small class="text-secondary text-truncate d-block" style="max-width: 190px;">{{ $settings['store_name'] }}</small>
                    </div>
                    <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-shop"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card kpi-card h-100 p-3 border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.72rem;">Base Currency &amp; Tax</div>
                        <div class="fw-bold text-dark mt-1">{{ $settings['currency_code'] }} ({{ $settings['currency_symbol'] }})</div>
                        <span class="badge bg-success-subtle text-success rounded-pill">
                            Default Tax: {{ number_format((float) $settings['default_tax_rate'], 2) }}%
                        </span>
                    </div>
                    <div class="kpi-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-currency-exchange"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card kpi-card h-100 p-3 border-start border-4 border-info">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.72rem;">POS Receipt Format</div>
                        <div class="fw-bold text-dark mt-1">{{ $settings['receipt_paper_size'] }} Thermal</div>
                        <span class="badge bg-info-subtle text-info-emphasis rounded-pill">
                            Prefix: {{ $settings['invoice_prefix'] }}
                        </span>
                    </div>
                    <div class="kpi-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-printer-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card kpi-card h-100 p-3 border-start border-4 border-warning">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.72rem;">Low Stock Alert Default</div>
                        <div class="fw-bold text-dark mt-1">{{ $settings['default_min_stock_alert'] }} Units</div>
                        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">
                            RBAC Policies Enforced
                        </span>
                    </div>
                    <div class="kpi-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ====================================================================
         PART 5: Main System Settings Form + Live Thermal Receipt Preview
         ==================================================================== --}}
    <form id="systemSettingsForm" action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- Left Column (8 cols on XL): Configuration Sections --}}
            <div class="col-12 col-xl-8">

                {{-- SECTION 1: Store Profile & Business Identity --}}
                <div class="card table-card mb-4">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-building text-primary me-2"></i>1. Store Profile &amp; Business Identity
                            </h6>
                            <small class="text-secondary">Displayed on POS thermal receipts, Purchase Orders, and analytical reports.</small>
                        </div>
                        <span class="badge bg-primary-subtle text-primary">Store Info</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold">Store / Company Name <span class="text-danger">*</span></label>
                                <input type="text" name="store_name" id="inputStoreName" class="form-control" required
                                       value="{{ old('store_name', $settings['store_name']) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Branch / Store Code <span class="text-danger">*</span></label>
                                <input type="text" name="store_code" class="form-control" required
                                       value="{{ old('store_code', $settings['store_code']) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Official Contact Email <span class="text-danger">*</span></label>
                                <input type="email" name="store_email" class="form-control" required
                                       value="{{ old('store_email', $settings['store_email']) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Store Hotline / Phone <span class="text-danger">*</span></label>
                                <input type="text" name="store_phone" id="inputStorePhone" class="form-control" required
                                       value="{{ old('store_phone', $settings['store_phone']) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Store Tagline / Subtitle</label>
                                <input type="text" name="store_tagline" class="form-control"
                                       value="{{ old('store_tagline', $settings['store_tagline']) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Tax / VAT Registration No.</label>
                                <input type="text" name="tax_registration_no" id="inputTaxReg" class="form-control"
                                       value="{{ old('tax_registration_no', $settings['tax_registration_no']) }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Physical Store Address <span class="text-danger">*</span></label>
                                <input type="text" name="store_address" id="inputStoreAddress" class="form-control" required
                                       value="{{ old('store_address', $settings['store_address']) }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: Currency, Tax / VAT & Localization --}}
                <div class="card table-card mb-4">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-cash-coin text-success me-2"></i>2. Currency, Tax / VAT &amp; Localization
                            </h6>
                            <small class="text-secondary">Controls pricing display, POS tax calculation, and cashier discount limits.</small>
                        </div>
                        <span class="badge bg-success-subtle text-success">Financial</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Currency Code <span class="text-danger">*</span></label>
                                <input type="text" name="currency_code" class="form-control" required
                                       value="{{ old('currency_code', $settings['currency_code']) }}" placeholder="LKR / USD">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Currency Symbol <span class="text-danger">*</span></label>
                                <input type="text" name="currency_symbol" id="inputCurrencySymbol" class="form-control" required
                                       value="{{ old('currency_symbol', $settings['currency_symbol']) }}" placeholder="LKR / $">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Default Tax / VAT (%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="50" name="default_tax_rate" class="form-control" required
                                           value="{{ old('default_tax_rate', $settings['default_tax_rate']) }}">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Max Cashier Discount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="1" min="0" max="100" name="max_cashier_discount_pct" class="form-control" required
                                           value="{{ old('max_cashier_discount_pct', $settings['max_cashier_discount_pct']) }}">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">System Timezone <span class="text-danger">*</span></label>
                                <select name="timezone" class="form-select">
                                    @foreach (['Asia/Colombo' => 'Asia/Colombo (GMT+05:30)', 'UTC' => 'UTC (GMT+00:00)', 'Asia/Dubai' => 'Asia/Dubai (GMT+04:00)', 'Asia/Singapore' => 'Asia/Singapore (GMT+08:00)', 'Europe/London' => 'Europe/London (GMT+00:00)'] as $tzVal => $tzLabel)
                                        <option value="{{ $tzVal }}" {{ old('timezone', $settings['timezone']) === $tzVal ? 'selected' : '' }}>
                                            {{ $tzLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Display Date Format <span class="text-danger">*</span></label>
                                <select name="date_format" class="form-select">
                                    @foreach (['d M Y' => '29 Oct 2026 (d M Y)', 'Y-m-d' => '2026-10-29 (Y-m-d)', 'd/m/Y' => '29/10/2026 (d/m/Y)', 'M d, Y' => 'Oct 29, 2026 (M d, Y)'] as $fmtVal => $fmtLabel)
                                        <option value="{{ $fmtVal }}" {{ old('date_format', $settings['date_format']) === $fmtVal ? 'selected' : '' }}>
                                            {{ $fmtLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION 3: POS Register, Document Prefixes & Thermal Receipt Customization --}}
                <div class="card table-card mb-4">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-receipt-cutoff text-info me-2"></i>3. POS Register, Document Prefixes &amp; Receipt Layout
                            </h6>
                            <small class="text-secondary">Customize document numbering prefixes and live thermal receipt header/footer text.</small>
                        </div>
                        <span class="badge bg-info-subtle text-info-emphasis">POS &amp; Receipts</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Sales Invoice Prefix <span class="text-danger">*</span></label>
                                <input type="text" name="invoice_prefix" id="inputInvoicePrefix" class="form-control font-monospace" required
                                       value="{{ old('invoice_prefix', $settings['invoice_prefix']) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Purchase Order Prefix <span class="text-danger">*</span></label>
                                <input type="text" name="po_prefix" class="form-control font-monospace" required
                                       value="{{ old('po_prefix', $settings['po_prefix']) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Stock Adj. Prefix <span class="text-danger">*</span></label>
                                <input type="text" name="adjustment_prefix" class="form-control font-monospace" required
                                       value="{{ old('adjustment_prefix', $settings['adjustment_prefix']) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Receipt Paper Size <span class="text-danger">*</span></label>
                                <select name="receipt_paper_size" class="form-select">
                                    <option value="80mm" {{ old('receipt_paper_size', $settings['receipt_paper_size']) === '80mm' ? 'selected' : '' }}>80mm Thermal Roll</option>
                                    <option value="58mm" {{ old('receipt_paper_size', $settings['receipt_paper_size']) === '58mm' ? 'selected' : '' }}>58mm Compact Roll</option>
                                    <option value="A4" {{ old('receipt_paper_size', $settings['receipt_paper_size']) === 'A4' ? 'selected' : '' }}>A4 Full Invoice</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Receipt Header Note</label>
                                <input type="text" name="receipt_header_note" id="inputReceiptHeader" class="form-control"
                                       value="{{ old('receipt_header_note', $settings['receipt_header_note']) }}"
                                       placeholder="e.g., Official Tax Invoice — Main Counter #01">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Receipt Footer Note / Return Policy</label>
                                <textarea name="receipt_footer_note" id="inputReceiptFooter" rows="2" class="form-control"
                                          placeholder="Thank you message and exchange policy...">{{ old('receipt_footer_note', $settings['receipt_footer_note']) }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check form-switch p-3 border rounded-3 bg-light">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" role="switch"
                                           id="switchAutoPrint" name="auto_print_receipt" value="1"
                                           {{ old('auto_print_receipt', $settings['auto_print_receipt']) === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold small" for="switchAutoPrint">
                                        Auto-Open Receipt Print Dialog After POS Checkout
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check form-switch p-3 border rounded-3 bg-light">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" role="switch"
                                           id="switchNegStock" name="allow_negative_stock_pos" value="1"
                                           {{ old('allow_negative_stock_pos', $settings['allow_negative_stock_pos']) === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold small" for="switchNegStock">
                                        Allow POS Sales When Product Stock is Zero
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION 4: Inventory Thresholds & Role-Based Security Policies --}}
                <div class="card table-card mb-4">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-shield-lock text-warning me-2"></i>4. Inventory Alerts &amp; Role Security Policies
                            </h6>
                            <small class="text-secondary">Global inventory alert defaults and role-based security rules.</small>
                        </div>
                        <span class="badge bg-warning-subtle text-warning-emphasis">Security &amp; Inventory</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Default Minimum Stock Alert Level <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="default_min_stock_alert" min="1" max="1000" class="form-control" required
                                           value="{{ old('default_min_stock_alert', $settings['default_min_stock_alert']) }}">
                                    <span class="input-group-text">Units</span>
                                </div>
                                <small class="text-secondary">Default reorder alert threshold when adding new products.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Idle Session Timeout <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="session_timeout_minutes" min="15" max="1440" class="form-control" required
                                           value="{{ old('session_timeout_minutes', $settings['session_timeout_minutes']) }}">
                                    <span class="input-group-text">Minutes</span>
                                </div>
                                <small class="text-secondary">Automatically signs out inactive staff sessions.</small>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-check form-switch p-3 border rounded-3 bg-light h-100">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" role="switch"
                                           id="switchAdjApproval" name="require_adjustment_approval" value="1"
                                           {{ old('require_adjustment_approval', $settings['require_adjustment_approval']) === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold small d-block" for="switchAdjApproval">
                                        Require Manager Approval for Stock Adjustments
                                    </label>
                                    <small class="text-secondary d-block mt-1">Stock Manager adjustments stay Pending until Admin/Store Manager approves.</small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-check form-switch p-3 border rounded-3 bg-light h-100">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" role="switch"
                                           id="switchHideCost" name="hide_cost_price_from_cashier" value="1"
                                           {{ old('hide_cost_price_from_cashier', $settings['hide_cost_price_from_cashier']) === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold small d-block" for="switchHideCost">
                                        Hide Cost Price &amp; Profit Margin from Cashiers
                                    </label>
                                    <small class="text-secondary d-block mt-1">Cashiers see selling price &amp; stock only in Products List.</small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-check form-switch p-3 border rounded-3 bg-light h-100">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" role="switch"
                                           id="switchCashierShift" name="restrict_cashier_own_shift_sales" value="1"
                                           {{ old('restrict_cashier_own_shift_sales', $settings['restrict_cashier_own_shift_sales']) === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold small d-block" for="switchCashierShift">
                                        Restrict Cashiers to Own-Shift Invoices Only
                                    </label>
                                    <small class="text-secondary d-block mt-1">Cashiers only view sales processed under their own login.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-white py-3 px-4 d-flex justify-content-end gap-2 border-top">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Save System Configuration
                        </button>
                    </div>
                </div>

            </div>

            {{-- Right Column (4 cols on XL): Live Thermal Receipt Preview & System Environment Diagnostics --}}
            <div class="col-12 col-xl-4">

                {{-- Live 80mm Thermal Receipt Preview Card --}}
                <div class="card table-card mb-4">
                    <div class="card-header bg-dark text-white py-3 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0">
                                <i class="bi bi-printer me-2 text-info"></i>Live Thermal Receipt Preview
                            </h6>
                            <small class="text-white-50">Updates in real time as you type</small>
                        </div>
                        <span class="badge bg-info text-dark">{{ $settings['receipt_paper_size'] }}</span>
                    </div>
                    <div class="card-body p-4 bg-light">
                        <div class="bg-white border rounded-3 shadow-sm p-3 mx-auto font-monospace" style="max-width: 320px; font-size: 0.78rem;">
                            <div class="text-center border-bottom border-dark border-opacity-25 pb-2 mb-2">
                                <div id="previewStoreName" class="fw-bold fs-6">{{ $settings['store_name'] }}</div>
                                <div id="previewStoreAddress" class="text-secondary small">{{ $settings['store_address'] }}</div>
                                <div class="text-secondary small">
                                    Tel: <span id="previewStorePhone">{{ $settings['store_phone'] }}</span>
                                </div>
                                <div class="text-secondary small">
                                    Tax Reg: <span id="previewTaxReg">{{ $settings['tax_registration_no'] }}</span>
                                </div>
                                <div id="previewHeaderNote" class="badge bg-light text-dark border mt-1 text-wrap">
                                    {{ $settings['receipt_header_note'] }}
                                </div>
                            </div>

                            <div class="d-flex justify-content-between small mb-1">
                                <span>Invoice: <strong id="previewInvoiceNo">{{ $settings['invoice_prefix'] }}1042</strong></span>
                                <span>{{ now()->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="d-flex justify-content-between small border-bottom border-dark border-opacity-25 pb-2 mb-2">
                                <span>Cashier: {{ auth()->user()->name }}</span>
                                <span>Pay: CASH</span>
                            </div>

                            <div class="mb-2">
                                <div class="d-flex justify-content-between">
                                    <span>1x Wireless Barcode Scanner</span>
                                    <span>12,900.00</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>2x Thermal Paper Box 80mm</span>
                                    <span>13,000.00</span>
                                </div>
                            </div>

                            <div class="border-top border-bottom border-dark border-opacity-25 py-2 my-2">
                                <div class="d-flex justify-content-between">
                                    <span>Subtotal:</span>
                                    <span>25,900.00</span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold fs-6 mt-1">
                                    <span>GRAND TOTAL (<span id="previewCurrency">{{ $settings['currency_symbol'] }}</span>):</span>
                                    <span>25,900.00</span>
                                </div>
                            </div>

                            <div id="previewFooterNote" class="text-center text-secondary small mt-2">
                                {{ $settings['receipt_footer_note'] }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- System Environment & Database Health Card --}}
                <div class="card table-card">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-cpu text-primary me-2"></i>System Environment &amp; Database
                            </h6>
                            <small class="text-secondary">Live runtime &amp; table record counts</small>
                        </div>
                        <span class="badge bg-success-subtle text-success">Healthy</span>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-2">
                                <span class="text-secondary">Laravel Framework</span>
                                <span class="fw-semibold badge bg-light text-dark border">v{{ $systemHealth['laravel_version'] }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-2">
                                <span class="text-secondary">PHP Runtime</span>
                                <span class="fw-semibold badge bg-light text-dark border">v{{ $systemHealth['php_version'] }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-2">
                                <span class="text-secondary">Database Connection</span>
                                <span class="fw-semibold text-uppercase badge bg-primary-subtle text-primary">{{ $systemHealth['database_driver'] }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-2">
                                <span class="text-secondary">Registered Staff Users</span>
                                <a href="{{ route('admin.users.index') }}" class="fw-semibold text-decoration-none">{{ $systemHealth['users_count'] }} Accounts</a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-2">
                                <span class="text-secondary">Catalog Products (SKUs)</span>
                                <a href="{{ route('admin.products.index') }}" class="fw-semibold text-decoration-none">{{ $systemHealth['products_count'] }} Products</a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-2">
                                <span class="text-secondary">Active Suppliers</span>
                                <a href="{{ route('admin.suppliers.index') }}" class="fw-semibold text-decoration-none">{{ $systemHealth['suppliers_count'] }} Suppliers</a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-2">
                                <span class="text-secondary">Purchase Orders / Adjustments</span>
                                <span class="fw-semibold">{{ $systemHealth['purchase_orders_count'] }} POs · {{ $systemHealth['adjustments_count'] }} Adj</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-2">
                                <span class="text-secondary">POS Sales Invoices</span>
                                <a href="{{ route('admin.sales.index') }}" class="fw-semibold text-decoration-none">{{ $systemHealth['pos_sales_count'] }} Invoices</a>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </form>

@endsection

{{-- ========================================================================
     PART 6: Real-Time Thermal Receipt Preview Script
     ======================================================================== --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bindings = [
            { input: 'inputStoreName', preview: 'previewStoreName' },
            { input: 'inputStoreAddress', preview: 'previewStoreAddress' },
            { input: 'inputStorePhone', preview: 'previewStorePhone' },
            { input: 'inputTaxReg', preview: 'previewTaxReg' },
            { input: 'inputReceiptHeader', preview: 'previewHeaderNote' },
            { input: 'inputReceiptFooter', preview: 'previewFooterNote' },
            { input: 'inputCurrencySymbol', preview: 'previewCurrency' },
        ];

        bindings.forEach(function (pair) {
            const inputEl = document.getElementById(pair.input);
            const previewEl = document.getElementById(pair.preview);
            if (inputEl && previewEl) {
                inputEl.addEventListener('input', function () {
                    previewEl.textContent = inputEl.value;
                });
            }
        });

        const prefixInput = document.getElementById('inputInvoicePrefix');
        const previewInvoice = document.getElementById('previewInvoiceNo');
        if (prefixInput && previewInvoice) {
            prefixInput.addEventListener('input', function () {
                previewInvoice.textContent = (prefixInput.value || 'INV-') + '1042';
            });
        }
    });
</script>
@endpush
