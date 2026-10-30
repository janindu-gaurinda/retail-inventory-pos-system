{{--
================================================================================
FILE PATH: resources/views/admin/products/barcodes.blade.php
DESCRIPTION: Dedicated "Barcode / Label Print" Page UI (Module 4 of ISM System).
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
- Route (GET /admin/products/barcodes): routes/web.php -> ProductController@barcodes ('admin.products.barcodes')
- Backend Controller: app/Http/Controllers/Admin/ProductController.php
- Product Model: app/Models/Product.php
================================================================================
--}}
@extends('layouts.admin')

{{-- =======================================================================
     PART 1: Dynamic Navbar Header Sections (Injected into partials/navbar.blade.php)
     ======================================================================= --}}
@section('title', 'Barcode / Label Print | ISM System')
@section('page_title', 'Barcode / Label Print')
@section('page_badge')
    <span class="badge text-bg-primary bg-opacity-10 text-primary border border-primary-subtle">
        Thermal &amp; A4 Sticker Generator
    </span>
@endsection
@section('page_subtitle', 'Select products, customize label fields, and print barcode sticker sheets')

@section('topbar_actions')
    <button type="button" class="btn btn-dark btn-sm fw-semibold d-inline-flex align-items-center gap-1 px-3 py-2 rounded-3"
            onclick="window.print()">
        <i class="bi bi-printer-fill"></i> Print Labels Now
    </button>
    <a href="{{ route('admin.products.index') }}" class="btn btn-light border btn-sm fw-semibold d-none d-md-inline-flex align-items-center gap-1 px-3 py-2 rounded-3">
        <i class="bi bi-box-seam"></i> Products List
    </a>
@endsection

{{-- =======================================================================
     PART 2: Main Barcode / Label Print Content (Injected into layouts/admin.blade.php)
     ======================================================================= --}}
@section('content')

    <div class="row g-4">

        {{-- ===============================================================
             SECTION A: Left Column — Label Configuration & Product Queue Builder
             (Hidden automatically when printing via .no-print class)
             =============================================================== --}}
        <div class="col-12 col-xl-4 no-print">

            <!-- Card 1: Add Product to Print Queue -->
            <div class="content-card p-3 p-sm-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <span class="section-badge">1</span>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Select Product &amp; Quantity</h6>
                        <small class="text-muted">Add one or multiple products to the label sheet</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="productSelect" class="form-label small fw-semibold">Choose Product</label>
                    <select id="productSelect" class="form-select form-select-sm">
                        @foreach ($products as $prod)
                            <option
                                value="{{ $prod->id }}"
                                data-name="{{ $prod->name }}"
                                data-sku="{{ $prod->sku }}"
                                data-barcode="{{ $prod->barcode }}"
                                data-price="{{ number_format((float) $prod->selling_price, 2) }}"
                                data-category="{{ $prod->category }}"
                                data-stock="{{ $prod->stock_quantity }}"
                                {{ $selectedProductId === $prod->id ? 'selected' : '' }}
                            >
                                {{ $prod->name }} ({{ $prod->sku }}) — LKR {{ number_format((float) $prod->selling_price, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="labelQty" class="form-label small fw-semibold">Number of Labels</label>
                        <input type="number" id="labelQty" class="form-control form-control-sm" min="1" max="200" value="6">
                    </div>
                    <div class="col-6 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="matchCurrentStock()">
                            <i class="bi bi-layers me-1"></i> Match Stock Qty
                        </button>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary btn-sm fw-semibold py-2" onclick="addSelectedProductToQueue()">
                        <i class="bi bi-plus-circle me-1"></i> Add to Label Sheet
                    </button>
                </div>
            </div>

            <!-- Card 2: Label Layout & Field Display Options -->
            <div class="content-card p-3 p-sm-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <span class="section-badge">2</span>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Label Format &amp; Display Settings</h6>
                        <small class="text-muted">Customize paper columns and visible fields</small>
                    </div>
                </div>

                <!-- Store Header Text -->
                <div class="mb-3">
                    <label for="storeHeaderInput" class="form-label small fw-semibold">Store / Header Title</label>
                    <input type="text" id="storeHeaderInput" class="form-control form-control-sm" value="{{ $systemSettings['store_name'] ?? 'ISM RETAIL STORE' }}" oninput="renderLabelSheet()">
                </div>

                <!-- Grid Columns (Paper Format) -->
                <div class="mb-3">
                    <label for="sheetColumns" class="form-label small fw-semibold">Sheet Layout / Columns</label>
                    <select id="sheetColumns" class="form-select form-select-sm" onchange="renderLabelSheet()">
                        <option value="2">2 Columns (Large Shelf Tags — 50x30mm)</option>
                        <option value="3" selected>3 Columns (Standard A4 Sheet — 38x25mm)</option>
                        <option value="4">4 Columns (Compact Stickers — 30x20mm)</option>
                    </select>
                </div>

                <!-- Field Toggles -->
                <div class="small fw-semibold text-secondary mb-2">Visible Label Elements:</div>
                <div class="row g-2 small">
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="showStoreName" checked onchange="renderLabelSheet()">
                            <label class="form-check-label" for="showStoreName">Store Header</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="showProductName" checked onchange="renderLabelSheet()">
                            <label class="form-check-label" for="showProductName">Product Name</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="showSellingPrice" checked onchange="renderLabelSheet()">
                            <label class="form-check-label" for="showSellingPrice">Selling Price</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="showSkuCode" checked onchange="renderLabelSheet()">
                            <label class="form-check-label" for="showSkuCode">SKU Code</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Queued Products Summary -->
            <div class="content-card p-3 p-sm-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-list-check text-primary me-1"></i> Queued Products
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="clearQueue()">
                        Clear All
                    </button>
                </div>
                <div id="queueListContainer" class="small d-grid gap-2 mt-2"></div>
            </div>

        </div>

        {{-- ===============================================================
             SECTION B: Right Column — Live Printable Barcode Sheet Preview
             =============================================================== --}}
        <div class="col-12 col-xl-8">
            <div class="content-card p-3 p-sm-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-2 border-bottom no-print">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-printer text-primary me-1"></i> Live Printable Barcode Sheet
                        </h6>
                        <small class="text-muted">Click "Print Labels Now" to print on a thermal label printer or A4 sticker sheet</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge text-bg-primary" id="totalLabelsBadge">0 Labels</span>
                        <button type="button" class="btn btn-dark btn-sm fw-semibold" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Print Sheet
                        </button>
                    </div>
                </div>

                <!-- Dynamic Barcode Stickers Grid -->
                <div id="barcodeSheetGrid" class="barcode-sheet-grid barcode-cols-3"></div>
            </div>
        </div>

    </div>

@endsection

{{-- =======================================================================
     PART 3: Page-Specific Scripts (Injected into @stack('scripts') in Layout)
     ======================================================================= --}}
@push('scripts')
<script>
    // Queue array storing items to print: [{ id, name, sku, barcode, price, qty }]
    let printQueue = [];

    const productSelect = document.getElementById('productSelect');
    const labelQtyInput = document.getElementById('labelQty');

    // Set quantity input equal to selected product's current stock
    function matchCurrentStock() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        if (!selectedOption) return;
        const stock = parseInt(selectedOption.getAttribute('data-stock'), 10) || 1;
        labelQtyInput.value = Math.max(1, stock);
    }

    // Add currently selected product to print queue
    function addSelectedProductToQueue() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        if (!selectedOption) return;

        const id = selectedOption.value;
        const name = selectedOption.getAttribute('data-name');
        const sku = selectedOption.getAttribute('data-sku');
        const barcode = selectedOption.getAttribute('data-barcode');
        const price = selectedOption.getAttribute('data-price');
        const qty = Math.min(200, Math.max(1, parseInt(labelQtyInput.value, 10) || 1));

        const existing = printQueue.find(item => item.id === id);
        if (existing) {
            existing.qty = Math.min(200, existing.qty + qty);
        } else {
            printQueue.push({ id, name, sku, barcode, price, qty });
        }

        renderLabelSheet();
    }

    // Remove single item from queue
    function removeFromQueue(id) {
        printQueue = printQueue.filter(item => item.id !== String(id));
        renderLabelSheet();
    }

    // Clear all items from queue
    function clearQueue() {
        printQueue = [];
        renderLabelSheet();
    }

    // Render both the Queue Summary list and the Printable Barcode Sheet
    function renderLabelSheet() {
        const grid = document.getElementById('barcodeSheetGrid');
        const queueContainer = document.getElementById('queueListContainer');
        const totalBadge = document.getElementById('totalLabelsBadge');

        const cols = document.getElementById('sheetColumns').value;
        grid.className = 'barcode-sheet-grid barcode-cols-' + cols;

        const storeHeader = document.getElementById('storeHeaderInput').value.trim() || 'ISM RETAIL STORE';
        const showStore = document.getElementById('showStoreName').checked;
        const showName = document.getElementById('showProductName').checked;
        const showPrice = document.getElementById('showSellingPrice').checked;
        const showSku = document.getElementById('showSkuCode').checked;

        // Render Queue List
        queueContainer.innerHTML = '';
        let totalLabels = 0;

        if (printQueue.length === 0) {
            queueContainer.innerHTML = '<div class="text-muted text-center py-2">No products in queue. Add a product above.</div>';
            grid.innerHTML = '<div class="text-center text-muted py-5" style="grid-column: 1 / -1;"><i class="bi bi-upc-scan fs-1 d-block mb-2"></i>Select a product on the left and click <strong>Add to Label Sheet</strong>.</div>';
            totalBadge.textContent = '0 Labels';
            return;
        }

        let cardsHtml = '';

        printQueue.forEach(item => {
            totalLabels += item.qty;

            // Queue row item
            queueContainer.innerHTML += `
                <div class="d-flex justify-content-between align-items-center p-2 border rounded bg-light">
                    <div class="text-truncate me-2">
                        <div class="fw-semibold text-dark text-truncate">${item.name}</div>
                        <small class="text-muted"><code>${item.sku}</code> &bull; <strong>${item.qty} labels</strong></small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeFromQueue('${item.id}')">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;

            // Individual barcode sticker cards
            for (let i = 0; i < item.qty; i++) {
                cardsHtml += `
                    <div class="barcode-label-card">
                        ${showStore ? `<div class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 0.08em;">${storeHeader}</div>` : ''}
                        ${showName ? `<div class="fw-bold text-dark small text-truncate my-1">${item.name}</div>` : ''}
                        <div class="barcode-bars"></div>
                        <div class="font-monospace fw-bold small" style="letter-spacing: 0.15em;">*${item.barcode}*</div>
                        ${showPrice ? `<div class="fw-bold text-primary mt-1" style="font-size: 0.95rem;">LKR ${item.price}</div>` : ''}
                        ${showSku ? `<div class="text-muted" style="font-size: 0.68rem;">SKU: ${item.sku}</div>` : ''}
                    </div>
                `;
            }
        });

        grid.innerHTML = cardsHtml;
        totalBadge.textContent = totalLabels + (totalLabels === 1 ? ' Label' : ' Labels');
    }

    // Automatically load the default selected product into the preview on initial page load
    document.addEventListener('DOMContentLoaded', function () {
        if (productSelect && productSelect.options.length > 0) {
            addSelectedProductToQueue();
        }
    });
</script>
@endpush
