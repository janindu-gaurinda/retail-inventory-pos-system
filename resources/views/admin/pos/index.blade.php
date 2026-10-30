{{--
================================================================================
FILE PATH: resources/views/admin/pos/index.blade.php
DESCRIPTION: POS Checkout Screen UI (Module 8 of ISM System).
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
- Route (GET /admin/pos): routes/web.php -> PosController@index ('admin.pos.index')
- Route (POST /admin/pos/checkout): routes/web.php -> PosController@checkout ('admin.pos.checkout')
- Backend Controller: app/Http/Controllers/Admin/PosController.php
- Sale & SaleItem Models: app/Models/Sale.php & app/Models/SaleItem.php
================================================================================
--}}
@extends('layouts.admin')

{{-- =======================================================================
     PART 1: Dynamic Navbar Header Sections (Injected into partials/navbar.blade.php)
     ======================================================================= --}}
@section('title', 'POS Checkout Terminal | ISM System')
@section('page_title', 'POS Checkout Screen')
@section('page_badge')
    <span class="badge text-bg-success">
        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> LIVE REGISTER
    </span>
@endsection
@section('page_subtitle', 'Scan barcode or click products to add to cart, calculate change, and print receipt')

@section('topbar_actions')
    <button type="button" class="btn btn-outline-danger btn-sm fw-semibold" onclick="clearCart()">
        <i class="bi bi-trash3 me-1"></i> Clear Cart
    </button>
@endsection

{{-- =======================================================================
     PART 2: Main POS Checkout Content (Injected into layouts/admin.blade.php)
     ======================================================================= --}}
@section('content')

    <div class="row g-4 no-print">

        {{-- ===============================================================
             SECTION A: Left Column (7 cols) — Barcode Scanner Input, Category Tabs & Product Grid
             =============================================================== --}}
        <div class="col-12 col-xl-7">

            <!-- Barcode Scanner & Search Bar -->
            <div class="content-card p-3 mb-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white border-primary">
                                <i class="bi bi-upc-scan"></i>
                            </span>
                            <input
                                type="text"
                                id="posSearchInput"
                                class="form-control"
                                placeholder="Scan Barcode (Press Enter) or search product name / SKU..."
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="col-12 col-md-5">
                        <select id="posCategoryFilter" class="form-select" onchange="filterPosProducts()">
                            <option value="">All Categories ({{ $products->count() }} items)</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Interactive Product Tiles Grid -->
            <div class="row g-3" id="posProductGrid">
                @foreach ($products as $product)
                    @php
                        $isOut = $product->stock_quantity <= 0;
                    @endphp
                    <div class="col-6 col-sm-4 pos-product-item"
                         data-id="{{ $product->id }}"
                         data-name="{{ strtolower($product->name) }}"
                         data-sku="{{ strtolower($product->sku) }}"
                         data-barcode="{{ $product->barcode }}"
                         data-category="{{ $product->category }}">
                        <div class="pos-product-card p-3 h-100 d-flex flex-column justify-content-between {{ $isOut ? 'out-of-stock' : '' }}"
                             @if (! $isOut)
                                 onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->sku }}', {{ (float) $product->selling_price }}, {{ $product->stock_quantity }})"
                             @endif>
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge text-bg-light border font-monospace" style="font-size: 0.68rem;">
                                        {{ $product->sku }}
                                    </span>
                                    @if ($isOut)
                                        <span class="badge text-bg-danger">Out of Stock</span>
                                    @elseif ($product->stock_quantity <= $product->min_stock_level)
                                        <span class="badge text-bg-warning">Stock: {{ $product->stock_quantity }}</span>
                                    @else
                                        <span class="badge text-bg-success bg-opacity-10 text-success">Stock: {{ $product->stock_quantity }}</span>
                                    @endif
                                </div>
                                <div class="fw-semibold text-dark small mb-1">{{ $product->name }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">
                                    <i class="bi bi-upc me-1"></i>{{ $product->barcode }}
                                </div>
                            </div>

                            <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary">
                                    LKR {{ number_format((float) $product->selling_price, 2) }}
                                </span>
                                <span class="btn btn-sm btn-primary py-0 px-2">
                                    <i class="bi bi-plus-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Recent Shift Invoices Strip -->
            @if ($recentSales->isNotEmpty())
                <div class="content-card p-3 mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0 small text-dark">
                            <i class="bi bi-clock-history text-primary me-1"></i> Recent POS Invoices (Click to View Receipt)
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Method</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentSales as $sale)
                                    <tr>
                                        <td class="fw-bold text-primary"><code>{{ $sale->invoice_no }}</code></td>
                                        <td>{{ $sale->customer_name }}</td>
                                        <td>{{ $sale->items_count }}</td>
                                        <td><span class="badge text-bg-light border">{{ $sale->payment_method }}</span></td>
                                        <td class="text-end fw-semibold">LKR {{ number_format((float) $sale->grand_total, 2) }}</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-dark py-0 px-2"
                                                    onclick='openReceiptModal(@json($sale))'>
                                                <i class="bi bi-printer"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>

        {{-- ===============================================================
             SECTION B: Right Column (5 cols) — Active POS Cart & Payment Checkout Form
             Submits POST to: route('admin.pos.checkout') -> PosController@checkout
             =============================================================== --}}
        <div class="col-12 col-xl-5">
            <div class="content-card p-3 p-sm-4 pos-cart-panel">
                <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-cart4 text-primary me-1"></i> Current Sale Basket
                        </h6>
                        <small class="text-muted">Cashier: {{ auth()->user()->name }}</small>
                    </div>
                    <span class="badge rounded-pill text-bg-primary" id="cartCountBadge">0 Items</span>
                </div>

                <!-- Cart Items Table -->
                <div class="table-responsive mb-3" style="max-height: 270px; overflow-y: auto;">
                    <table class="table table-sm align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th class="text-center" style="width: 110px;">Qty</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody id="posCartTableBody">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bi bi-cart-x fs-3 d-block mb-1"></i>
                                    Cart is empty. Click a product or scan a barcode.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Checkout Form -->
                <form action="{{ route('admin.pos.checkout') }}" method="POST" id="posCheckoutForm" onsubmit="return validatePosCheckout()">
                    @csrf
                    <input type="hidden" name="cart_json" id="cartJsonInput" value="[]">

                    <!-- Customer / Order Channel Selector + Optional Custom Name & Phone -->
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label small fw-semibold text-secondary mb-1">Customer / Sale Type</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light"><i class="bi bi-person-badge"></i></span>
                                <input type="text"
                                       name="customer_name"
                                       id="posCustomerInput"
                                       list="posCustomerList"
                                       class="form-control form-control-sm"
                                       value="Walk-in Customer (In-Store)"
                                       placeholder="Select or type customer / channel...">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Quick Select Channel"></button>
                                <ul class="dropdown-menu dropdown-menu-end small">
                                    @foreach ($customerOptions ?? ['Walk-in Customer (In-Store)', 'Online Order (Website / Social)', 'Wholesale / Trade Buyer', 'Phone / WhatsApp Delivery Order'] as $custOpt)
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="selectPosCustomer('{{ addslashes($custOpt) }}'); return false;">
                                                {{ $custOpt }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <datalist id="posCustomerList">
                                @foreach ($customerOptions ?? ['Walk-in Customer (In-Store)', 'Online Order (Website / Social)', 'Wholesale / Trade Buyer', 'Phone / WhatsApp Delivery Order'] as $custOpt)
                                    <option value="{{ $custOpt }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-5">
                            <label class="form-label small fw-semibold text-secondary mb-1">Phone (Optional)</label>
                            <input type="text" name="customer_phone" class="form-control form-control-sm" placeholder="07X XXX XXXX">
                        </div>
                    </div>

                    <!-- Two-Way Discount (%) & Discount (LKR) + Tax / VAT (%) Inputs -->
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label small fw-semibold text-primary mb-1">Discount (%)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.01" min="0" max="100" id="posDiscountPctInput"
                                       class="form-control form-control-sm" value="0" oninput="onDiscountPctInput()">
                                <span class="input-group-text bg-light">%</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">Discount (LKR)</label>
                            <input type="number" step="0.01" min="0" name="discount_amount" id="posDiscountInput"
                                   class="form-control form-control-sm" value="0.00" oninput="onDiscountAmtInput()">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold text-secondary mb-1">Tax / VAT (%)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.1" min="0" max="100" name="tax_rate" id="posTaxRateInput"
                                       class="form-control form-control-sm" value="{{ $systemSettings['default_tax_rate'] ?? '0' }}" oninput="recalcTotals()">
                                <span class="input-group-text bg-light">%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Totals Summary Box -->
                    <div class="bg-light rounded-3 p-3 border mb-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary">Subtotal:</span>
                            <span class="fw-semibold" id="summarySubtotal">LKR 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary">Discount (<span id="summaryDiscountPct">0.00%</span>):</span>
                            <span class="text-danger fw-semibold" id="summaryDiscount">- LKR 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span class="text-secondary">Tax Amount:</span>
                            <span class="fw-medium" id="summaryTax">LKR 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark fs-6">GRAND TOTAL:</span>
                            <span class="fw-bold text-primary fs-5" id="summaryGrandTotal">LKR 0.00</span>
                        </div>
                    </div>

                    <!-- Payment Method & Customer Given Cash Calculator -->
                    <div class="row g-2 mb-2">
                        <div class="col-5">
                            <label class="form-label small fw-semibold mb-1">Payment Method</label>
                            <select name="payment_method" id="posPaymentMethod" class="form-select form-select-sm" onchange="syncPaidAmount()">
                                <option value="Cash">Cash</option>
                                <option value="Card">Card (VISA/Master)</option>
                                <option value="QR / Bank">QR / Bank Transfer</option>
                            </select>
                        </div>
                        <div class="col-7">
                            <label class="form-label small fw-semibold text-primary mb-1">Customer Given Amount (LKR) *</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light fw-semibold">LKR</span>
                                <input type="number" step="0.01" min="0" name="paid_amount" id="posPaidInput"
                                       class="form-control form-control-sm fw-bold text-dark" value="0.00" required
                                       oninput="onPaidManualInput()">
                            </div>
                        </div>
                    </div>

                    <!-- Quick Cash Note Buttons (Set Tendered Note or Add to Cash) -->
                    <div class="d-flex flex-wrap gap-1 mb-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" onclick="setExactCash()">Exact Bill</button>
                        <button type="button" class="btn btn-sm btn-outline-primary flex-fill" onclick="setQuickCash(500)">500</button>
                        <button type="button" class="btn btn-sm btn-outline-primary flex-fill" onclick="setQuickCash(1000)">1,000</button>
                        <button type="button" class="btn btn-sm btn-outline-primary flex-fill" onclick="setQuickCash(2000)">2,000</button>
                        <button type="button" class="btn btn-sm btn-outline-primary flex-fill" onclick="setQuickCash(5000)">5,000</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addQuickCash(1000)" title="Add +1,000 to current amount">+1K</button>
                    </div>

                    <!-- Live Balance / Change Calculator Box -->
                    <div id="posBalanceBox" class="p-3 rounded-3 bg-success bg-opacity-10 border border-success-subtle mb-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary">Bill Grand Total:</span>
                            <span class="fw-semibold text-dark" id="calcBillTotal">LKR 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom border-success border-opacity-25">
                            <span class="text-secondary">Customer Given Cash:</span>
                            <span class="fw-bold text-primary" id="calcCustomerGiven">LKR 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-success fs-6" id="posChangeLabel">Balance / Change to Return:</span>
                            <span class="fw-bold text-success fs-5" id="posChangeDue">LKR 0.00</span>
                        </div>
                    </div>

                    <!-- Complete Sale Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success fw-bold py-2 fs-6" id="completeSaleBtn" disabled>
                            <i class="bi bi-check2-circle me-1"></i> Complete Sale &amp; Print Receipt
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

@endsection

{{-- =======================================================================
     PART 3: Printable Thermal Receipt Modal (Injected into @stack('modals'))
     ======================================================================= --}}
@push('modals')
    <div class="modal fade" id="posReceiptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white no-print">
                    <h6 class="modal-title fw-bold">
                        <i class="bi bi-receipt me-2"></i>POS Customer Receipt
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="pos-receipt-box small">
                        <div class="text-center border-bottom pb-2 mb-2">
                            <div class="fw-bold fs-6 text-uppercase">{{ $systemSettings['store_name'] ?? 'ISM RETAIL STORE' }}</div>
                            <div class="text-muted" style="font-size: 0.72rem;">
                                {{ $systemSettings['store_address'] ?? 'Colombo, Sri Lanka' }}
                            </div>
                            <div class="text-muted" style="font-size: 0.72rem;">
                                Tel: {{ $systemSettings['store_phone'] ?? '+94 11 234 5678' }}
                                @if (!empty($systemSettings['store_email']))
                                    &bull; {{ $systemSettings['store_email'] }}
                                @endif
                            </div>
                            @if (!empty($systemSettings['tax_registration_no']))
                                <div class="text-muted" style="font-size: 0.7rem;">
                                    Tax / VAT Reg: {{ $systemSettings['tax_registration_no'] }}
                                </div>
                            @endif
                            @if (!empty($systemSettings['receipt_header_note']))
                                <div class="badge bg-light text-dark border mt-1 text-wrap" style="font-size: 0.68rem;">
                                    {{ $systemSettings['receipt_header_note'] }}
                                </div>
                            @endif
                            <div class="fw-bold mt-1" id="rcp_invoice_no"></div>
                            <div class="text-muted" style="font-size: 0.7rem;" id="rcp_date"></div>
                        </div>

                        <div class="mb-2" style="font-size: 0.75rem;">
                            <div>Customer: <span id="rcp_customer"></span></div>
                            <div>Cashier: <span id="rcp_cashier"></span></div>
                            <div>Payment: <span id="rcp_method"></span></div>
                        </div>

                        <div class="border-top border-bottom py-2 mb-2" id="rcp_items_list" style="font-size: 0.75rem;"></div>

                        <div style="font-size: 0.78rem;">
                            <div class="d-flex justify-content-between">
                                <span>Subtotal:</span>
                                <span id="rcp_subtotal"></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Discount (<span id="rcp_discount_pct">0%</span>):</span>
                                <span id="rcp_discount"></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Tax:</span>
                                <span id="rcp_tax"></span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold border-top pt-1 mt-1">
                                <span>TOTAL:</span>
                                <span id="rcp_total"></span>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <span>Paid:</span>
                                <span id="rcp_paid"></span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Change:</span>
                                <span id="rcp_change"></span>
                            </div>
                        </div>

                        <div class="text-center border-top pt-2 mt-3" style="font-size: 0.7rem;">
                            {{ $systemSettings['receipt_footer_note'] ?? 'Thank you for shopping with ISM System!' }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-center no-print">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm fw-semibold" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush

{{-- =======================================================================
     PART 4: POS Cart, Barcode Scanner & Receipt Scripts
     ======================================================================= --}}
@push('scripts')
<script>
    let cart = [];
    let currentGrandTotal = 0;

    // Add product to POS cart
    function addToCart(id, name, sku, price, maxStock) {
        const existing = cart.find(item => item.id === id);
        if (existing) {
            if (existing.qty >= maxStock) {
                alert(`Maximum available stock (${maxStock}) reached for ${name}.`);
                return;
            }
            existing.qty += 1;
        } else {
            cart.push({ id, name, sku, price: parseFloat(price), qty: 1, maxStock: parseInt(maxStock, 10) });
        }
        renderCart();
    }

    // Change item quantity in cart
    function updateCartQty(id, delta) {
        const item = cart.find(i => i.id === id);
        if (!item) return;

        const nextQty = item.qty + delta;
        if (nextQty <= 0) {
            removeFromCart(id);
            return;
        }
        if (nextQty > item.maxStock) {
            alert(`Only ${item.maxStock} units available in stock.`);
            return;
        }
        item.qty = nextQty;
        renderCart();
    }

    // Remove item from cart
    function removeFromCart(id) {
        cart = cart.filter(i => i.id !== id);
        renderCart();
    }

    // Clear entire cart
    let userManuallyEnteredCash = false;

    function clearCart() {
        cart = [];
        userManuallyEnteredCash = false;
        const pctEl = document.getElementById('posDiscountPctInput');
        if (pctEl) pctEl.value = '0';
        document.getElementById('posDiscountInput').value = '0.00';
        document.getElementById('posTaxRateInput').value = '0';
        document.getElementById('posPaidInput').value = '0.00';
        renderCart();
    }

    // Select customer/channel from dropdown
    function selectPosCustomer(value) {
        const input = document.getElementById('posCustomerInput');
        if (input) input.value = value;
    }

    // Track whether the user last edited Discount (%) or Discount (LKR)
    let discountEditMode = 'amount'; // 'percent' or 'amount'

    // When user types in Discount (%) -> Auto-calculate Discount (LKR)
    function onDiscountPctInput() {
        discountEditMode = 'percent';
        const subtotal = cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
        const pct = Math.min(100, Math.max(0, parseFloat(document.getElementById('posDiscountPctInput').value) || 0));
        const amt = subtotal * (pct / 100);
        document.getElementById('posDiscountInput').value = amt.toFixed(2);
        recalcTotals();
    }

    // When user types in Discount (LKR) -> Auto-calculate Discount (%)
    function onDiscountAmtInput() {
        discountEditMode = 'amount';
        const subtotal = cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
        const amt = Math.min(subtotal, Math.max(0, parseFloat(document.getElementById('posDiscountInput').value) || 0));
        const pct = subtotal > 0 ? (amt / subtotal) * 100 : 0;
        document.getElementById('posDiscountPctInput').value = pct.toFixed(2);
        recalcTotals();
    }

    // Render cart table & recalculate totals
    function renderCart() {
        const tbody = document.getElementById('posCartTableBody');
        const countBadge = document.getElementById('cartCountBadge');
        const cartJsonInput = document.getElementById('cartJsonInput');
        const submitBtn = document.getElementById('completeSaleBtn');

        cartJsonInput.value = JSON.stringify(cart);

        const totalUnits = cart.reduce((sum, i) => sum + i.qty, 0);
        countBadge.textContent = totalUnits + (totalUnits === 1 ? ' Item' : ' Items');

        if (cart.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        <i class="bi bi-cart-x fs-3 d-block mb-1"></i>
                        Cart is empty. Click a product or scan a barcode.
                    </td>
                </tr>
            `;
            submitBtn.disabled = true;
            recalcTotals();
            return;
        }

        submitBtn.disabled = false;
        let rowsHtml = '';

        cart.forEach(item => {
            const lineTotal = item.price * item.qty;
            rowsHtml += `
                <tr>
                    <td>
                        <div class="fw-semibold text-dark text-truncate" style="max-width: 150px;">${item.name}</div>
                        <small class="text-muted">LKR ${item.price.toLocaleString(undefined, {minimumFractionDigits: 2})}</small>
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary py-0 px-2" onclick="updateCartQty(${item.id}, -1)">-</button>
                            <span class="btn btn-light border py-0 px-2 fw-bold">${item.qty}</span>
                            <button type="button" class="btn btn-outline-secondary py-0 px-2" onclick="updateCartQty(${item.id}, 1)">+</button>
                        </div>
                    </td>
                    <td class="text-end fw-semibold">
                        ${lineTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm text-danger p-0" onclick="removeFromCart(${item.id})">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = rowsHtml;
        recalcTotals();
    }

    // Calculate Subtotal, Discount (% & LKR), Tax & Grand Total
    function recalcTotals() {
        const subtotal = cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
        const pctInput = document.getElementById('posDiscountPctInput');
        const amtInput = document.getElementById('posDiscountInput');

        let discount = 0;
        let discountPct = 0;

        if (subtotal > 0) {
            if (discountEditMode === 'percent' && pctInput) {
                discountPct = Math.min(100, Math.max(0, parseFloat(pctInput.value) || 0));
                discount = Math.round((subtotal * (discountPct / 100)) * 100) / 100;
                amtInput.value = discount.toFixed(2);
            } else {
                discount = Math.min(subtotal, Math.max(0, parseFloat(amtInput.value) || 0));
                discountPct = (discount / subtotal) * 100;
                if (pctInput && document.activeElement !== pctInput) {
                    pctInput.value = discountPct > 0 ? discountPct.toFixed(2) : '0';
                }
            }
        } else {
            discount = 0;
            discountPct = 0;
        }

        const taxRate = parseFloat(document.getElementById('posTaxRateInput').value) || 0;

        const taxable = Math.max(0, subtotal - discount);
        const taxAmount = taxable * (taxRate / 100);
        currentGrandTotal = Math.round((taxable + taxAmount) * 100) / 100;

        document.getElementById('summarySubtotal').textContent = 'LKR ' + subtotal.toLocaleString(undefined, {minimumFractionDigits: 2});
        const summaryPctEl = document.getElementById('summaryDiscountPct');
        if (summaryPctEl) {
            summaryPctEl.textContent = discountPct.toFixed(2) + '%';
        }
        document.getElementById('summaryDiscount').textContent = '- LKR ' + discount.toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('summaryTax').textContent = 'LKR ' + taxAmount.toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('summaryGrandTotal').textContent = 'LKR ' + currentGrandTotal.toLocaleString(undefined, {minimumFractionDigits: 2});

        // Only auto-set paid amount to Grand Total if cashier hasn't typed a custom cash note yet
        const paidInput = document.getElementById('posPaidInput');
        if (!userManuallyEnteredCash && document.activeElement !== paidInput) {
            paidInput.value = currentGrandTotal.toFixed(2);
        }
        calcChangeDue();
    }

    function setExactCash() {
        userManuallyEnteredCash = false;
        document.getElementById('posPaidInput').value = currentGrandTotal.toFixed(2);
        calcChangeDue();
    }

    // Set exact note handed by customer (e.g. 500, 1000, 2000, 5000)
    function setQuickCash(noteAmount) {
        userManuallyEnteredCash = true;
        document.getElementById('posPaidInput').value = parseFloat(noteAmount).toFixed(2);
        calcChangeDue();
    }

    // Add to current customer given cash
    function addQuickCash(amount) {
        userManuallyEnteredCash = true;
        const currentPaid = parseFloat(document.getElementById('posPaidInput').value) || 0;
        document.getElementById('posPaidInput').value = (currentPaid + amount).toFixed(2);
        calcChangeDue();
    }

    function onPaidManualInput() {
        userManuallyEnteredCash = true;
        calcChangeDue();
    }

    function syncPaidAmount() {
        setExactCash();
    }

    function calcChangeDue() {
        const paid = parseFloat(document.getElementById('posPaidInput').value) || 0;
        const diff = Math.round((paid - currentGrandTotal) * 100) / 100;

        const billTotalEl = document.getElementById('calcBillTotal');
        const customerGivenEl = document.getElementById('calcCustomerGiven');
        const changeLabelEl = document.getElementById('posChangeLabel');
        const changeDueEl = document.getElementById('posChangeDue');
        const balanceBoxEl = document.getElementById('posBalanceBox');

        if (billTotalEl) {
            billTotalEl.textContent = 'LKR ' + currentGrandTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
        if (customerGivenEl) {
            customerGivenEl.textContent = 'LKR ' + paid.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        if (diff >= 0) {
            // Sufficient cash given -> show green Change to Return
            if (balanceBoxEl) {
                balanceBoxEl.className = 'p-3 rounded-3 bg-success bg-opacity-10 border border-success-subtle mb-3 small';
            }
            if (changeLabelEl) {
                changeLabelEl.className = 'fw-bold text-success fs-6';
                changeLabelEl.textContent = 'Balance / Change to Return:';
            }
            changeDueEl.className = 'fw-bold text-success fs-5';
            changeDueEl.textContent = 'LKR ' + diff.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        } else {
            // Customer gave less than Grand Total -> show red Balance Still Due
            const shortage = Math.abs(diff);
            if (balanceBoxEl) {
                balanceBoxEl.className = 'p-3 rounded-3 bg-danger bg-opacity-10 border border-danger-subtle mb-3 small';
            }
            if (changeLabelEl) {
                changeLabelEl.className = 'fw-bold text-danger fs-6';
                changeLabelEl.textContent = 'Shortage / Balance Still Due:';
            }
            changeDueEl.className = 'fw-bold text-danger fs-5';
            changeDueEl.textContent = '- LKR ' + shortage.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
    }

    function validatePosCheckout() {
        if (cart.length === 0) {
            alert('Please add at least one product to the cart.');
            return false;
        }
        const paid = parseFloat(document.getElementById('posPaidInput').value) || 0;
        if (paid < currentGrandTotal) {
            alert('Amount received cannot be less than the Grand Total.');
            return false;
        }
        return true;
    }

    // Product Search & Barcode Scanner Enter Key Handler
    const posSearchInput = document.getElementById('posSearchInput');
    const posCategoryFilter = document.getElementById('posCategoryFilter');

    function filterPosProducts() {
        const q = (posSearchInput.value || '').trim().toLowerCase();
        const cat = posCategoryFilter.value;

        document.querySelectorAll('.pos-product-item').forEach(el => {
            const name = el.getAttribute('data-name') || '';
            const sku = el.getAttribute('data-sku') || '';
            const barcode = el.getAttribute('data-barcode') || '';
            const itemCat = el.getAttribute('data-category') || '';

            const matchesText = !q || name.includes(q) || sku.includes(q) || barcode.includes(q);
            const matchesCat = !cat || itemCat === cat;

            el.style.display = (matchesText && matchesCat) ? '' : 'none';
        });
    }

    if (posSearchInput) {
        posSearchInput.addEventListener('input', filterPosProducts);

        // If user scans a barcode and hits Enter, immediately add matching product to cart
        posSearchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const q = posSearchInput.value.trim().toLowerCase();
                if (!q) return;

                const exactMatch = Array.from(document.querySelectorAll('.pos-product-item')).find(el => {
                    return el.getAttribute('data-barcode') === q || el.getAttribute('data-sku') === q;
                });

                if (exactMatch) {
                    const card = exactMatch.querySelector('.pos-product-card');
                    if (card && !card.classList.contains('out-of-stock')) {
                        card.click();
                        posSearchInput.value = '';
                        filterPosProducts();
                    }
                }
            }
        });
    }

    // Open Thermal Receipt Modal
    function openReceiptModal(sale) {
        document.getElementById('rcp_invoice_no').textContent = sale.invoice_no;
        document.getElementById('rcp_date').textContent = new Date(sale.created_at).toLocaleString();
        document.getElementById('rcp_customer').textContent = sale.customer_name;
        document.getElementById('rcp_cashier').textContent = sale.cashier ? sale.cashier.name : 'Cashier';
        document.getElementById('rcp_method').textContent = sale.payment_method;

        let itemsHtml = '';
        if (sale.items && sale.items.length) {
            sale.items.forEach(item => {
                itemsHtml += `
                    <div class="d-flex justify-content-between mb-1">
                        <span>${item.product_name} (x${item.quantity})</span>
                        <span>${Number(item.line_total).toLocaleString(undefined, {minimumFractionDigits: 2})}</span>
                    </div>
                `;
            });
        }
        document.getElementById('rcp_items_list').innerHTML = itemsHtml;

        const rcpSubtotal = Number(sale.subtotal) || 0;
        const rcpDiscount = Number(sale.discount_amount) || 0;
        const rcpDiscountPct = rcpSubtotal > 0 ? ((rcpDiscount / rcpSubtotal) * 100).toFixed(2) : '0.00';

        document.getElementById('rcp_subtotal').textContent = 'LKR ' + rcpSubtotal.toLocaleString(undefined, {minimumFractionDigits: 2});
        const rcpPctEl = document.getElementById('rcp_discount_pct');
        if (rcpPctEl) {
            rcpPctEl.textContent = rcpDiscountPct + '%';
        }
        document.getElementById('rcp_discount').textContent = '- LKR ' + rcpDiscount.toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('rcp_tax').textContent = 'LKR ' + Number(sale.tax_amount).toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('rcp_total').textContent = 'LKR ' + Number(sale.grand_total).toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('rcp_paid').textContent = 'LKR ' + Number(sale.paid_amount).toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('rcp_change').textContent = 'LKR ' + Number(sale.change_amount).toLocaleString(undefined, {minimumFractionDigits: 2});

        new bootstrap.Modal(document.getElementById('posReceiptModal')).show();
    }

    @if ($lastCompletedSale)
        document.addEventListener('DOMContentLoaded', function () {
            openReceiptModal(@json($lastCompletedSale));
        });
    @endif
</script>
@endpush
