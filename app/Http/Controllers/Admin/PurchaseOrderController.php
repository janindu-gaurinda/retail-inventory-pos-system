<?php

/**
 * ============================================================================
 * FILE PATH: app/Http/Controllers/Admin/PurchaseOrderController.php
 * DESCRIPTION: Full CRUD & Stock Receiving Backend Controller for Module 7 —
 *              Purchase Orders (POs).
 *              Supports:
 *              - READ: Search, Supplier Filter, Status Filter, Summary KPIs
 *              - CREATE: Create new Purchase Order
 *              - RECEIVE: Mark PO as Received -> Automatically increments
 *                         Product stock_quantity & Supplier outstanding_balance
 *              - CANCEL / DELETE: Cancel or delete PO records
 *              - ROLE PROTECTION: Admin, Store Manager, Stock Manager (✓); Cashier (❌ 403)
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Routes File: routes/web.php (Routes: 'admin.purchase-orders.*')
 * - PurchaseOrder Model (Database): app/Models/PurchaseOrder.php
 * - Supplier Model (Database): app/Models/Supplier.php
 * - Product Model (Stock Updates): app/Models/Product.php
 * - User Model (Permissions): app/Models/User.php
 * - Purchase Orders UI: resources/views/admin/purchase_orders/index.blade.php
 * ============================================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    /**
     * PART 1: Display the Purchase Orders Page UI with Filters & KPIs (READ).
     *
     * Route: GET /admin/purchase-orders (route name: 'admin.purchase-orders.index')
     * UI View Path: resources/views/admin/purchase_orders/index.blade.php
     */
    public function index(Request $request): View
    {
        if (! $request->user()->canAccessPurchaseOrders()) {
            abort(403, 'Unauthorized: Cashier role cannot access Purchase Orders.');
        }

        $search = trim((string) $request->query('search', ''));
        $supplierFilter = trim((string) $request->query('supplier_id', ''));
        $statusFilter = trim((string) $request->query('status', ''));

        $query = PurchaseOrder::with(['supplier', 'product', 'creator', 'receiver']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('company_name', 'like', "%{$search}%"))
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            });
        }

        if ($supplierFilter !== '') {
            $query->where('supplier_id', $supplierFilter);
        }

        if ($statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        $purchaseOrders = $query->orderByDesc('id')->get();

        // Calculate Summary KPIs across all Purchase Orders
        $allPOs = PurchaseOrder::all();
        $summary = [
            'total_pos' => $allPOs->count(),
            'open_pos' => $allPOs->where('status', 'ordered')->count(),
            'received_pos' => $allPOs->where('status', 'received')->count(),
            'total_open_value' => $allPOs->where('status', 'ordered')->sum(fn (PurchaseOrder $po) => (float) $po->total_amount),
            'total_received_value' => $allPOs->where('status', 'received')->sum(fn (PurchaseOrder $po) => (float) $po->total_amount),
        ];

        $suppliers = Supplier::where('status', 'active')->orderBy('company_name')->get();
        $products = Product::where('status', 'active')->orderBy('name')->get();

        return view('admin.purchase_orders.index', [
            'purchaseOrders' => $purchaseOrders,
            'summary' => $summary,
            'suppliers' => $suppliers,
            'products' => $products,
            'filters' => [
                'search' => $search,
                'supplier_id' => $supplierFilter,
                'status' => $statusFilter,
            ],
        ]);
    }

    /**
     * PART 2: Create a New Purchase Order (CREATE).
     *
     * Route: POST /admin/purchase-orders (route name: 'admin.purchase-orders.store')
     * Form Source: Create PO Modal in resources/views/admin/purchase_orders/index.blade.php
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->canAccessPurchaseOrders()) {
            abort(403, 'Unauthorized: Cashier role cannot create Purchase Orders.');
        }

        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'expected_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $qty = (int) $validated['quantity'];
        $unitCost = (float) $validated['unit_cost'];
        $totalAmount = round($qty * $unitCost, 2);

        $nextNumber = PurchaseOrder::max('id') + 1;
        $poPrefix = \App\Models\SystemSetting::getAllMerged()['po_prefix'] ?? ('PO-' . date('Y') . '-');
        $poNumber = $poPrefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);

        $po = PurchaseOrder::create([
            'po_number' => $poNumber,
            'supplier_id' => $validated['supplier_id'],
            'product_id' => $validated['product_id'],
            'quantity' => $qty,
            'unit_cost' => $unitCost,
            'total_amount' => $totalAmount,
            'expected_date' => $validated['expected_date'] ?? now()->addDays(5)->toDateString(),
            'status' => 'ordered',
            'notes' => $validated['notes'] ?? null,
            'created_by' => $user->id,
        ]);

        return redirect()
            ->route('admin.purchase-orders.index')
            ->with('success', "Purchase Order {$po->po_number} created for LKR " . number_format($totalAmount, 2) . '.');
    }

    /**
     * PART 3: Receive Goods for an Open Purchase Order (RECEIVE STOCK).
     *         Automatically increments Product stock_quantity, updates cost_price,
     *         and adds the PO total_amount to Supplier's outstanding_balance.
     *
     * Route: PATCH /admin/purchase-orders/{purchaseOrder}/receive
     */
    public function receive(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $user = $request->user();

        if (! $user->canAccessPurchaseOrders()) {
            abort(403, 'Unauthorized action.');
        }

        if ($purchaseOrder->status !== 'ordered') {
            return back()->with('success', "PO {$purchaseOrder->po_number} has already been processed.");
        }

        $product = $purchaseOrder->product;
        $supplier = $purchaseOrder->supplier;

        $oldStock = $product->stock_quantity;
        $newStock = $oldStock + $purchaseOrder->quantity;

        // 1. Increment product stock & update latest unit cost price
        $product->update([
            'stock_quantity' => $newStock,
            'cost_price' => $purchaseOrder->unit_cost,
        ]);

        // 2. Increment supplier payable balance
        if ($supplier) {
            $supplier->update([
                'outstanding_balance' => (float) $supplier->outstanding_balance + (float) $purchaseOrder->total_amount,
            ]);
        }

        // 3. Mark PO as received
        $purchaseOrder->update([
            'status' => 'received',
            'received_by' => $user->id,
            'received_at' => now(),
        ]);

        return redirect()
            ->route('admin.purchase-orders.index')
            ->with('success', "PO {$purchaseOrder->po_number} received! '{$product->name}' stock increased ({$oldStock} → {$newStock}).");
    }

    /**
     * PART 4: Cancel an Open Purchase Order (CANCEL).
     *
     * Route: PATCH /admin/purchase-orders/{purchaseOrder}/cancel
     */
    public function cancel(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if (! $request->user()->canAccessPurchaseOrders()) {
            abort(403, 'Unauthorized action.');
        }

        if ($purchaseOrder->status === 'ordered') {
            $purchaseOrder->update(['status' => 'cancelled']);
        }

        return redirect()
            ->route('admin.purchase-orders.index')
            ->with('success', "Purchase Order {$purchaseOrder->po_number} has been cancelled.");
    }

    /**
     * PART 5: Delete a Purchase Order Record (DELETE).
     *
     * Route: DELETE /admin/purchase-orders/{purchaseOrder}
     */
    public function destroy(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if (! $request->user()->canAccessPurchaseOrders()) {
            abort(403, 'Unauthorized action.');
        }

        $poNum = $purchaseOrder->po_number;
        $purchaseOrder->delete();

        return redirect()
            ->route('admin.purchase-orders.index')
            ->with('success', "Purchase Order {$poNum} deleted.");
    }
}
