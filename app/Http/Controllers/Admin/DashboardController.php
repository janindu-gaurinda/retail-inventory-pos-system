<?php

/**
 * ============================================================================
 * FILE PATH: app/Http/Controllers/Admin/DashboardController.php
 * DESCRIPTION: Backend Controller for the Admin Dashboard (Full - All Stats).
 *              Queries live data from Sales, Products, StockAdjustments,
 *              PurchaseOrders, Suppliers, and Users tables.
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Routes File: routes/web.php (Route: GET /admin/dashboard -> 'admin.dashboard')
 * - UI View (Dashboard Page): resources/views/admin/dashboard.blade.php
 * - Models Used:
 *   - app/Models/Sale.php
 *   - app/Models/Product.php
 *   - app/Models/StockAdjustment.php
 *   - app/Models/PurchaseOrder.php
 *   - app/Models/Supplier.php
 *   - app/Models/User.php
 * ============================================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\StockAdjustment;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * PART 1: Display the Admin Dashboard UI with Live Database Statistics.
     *
     * Route: GET /admin/dashboard (route name: 'admin.dashboard')
     * UI View Path: resources/views/admin/dashboard.blade.php
     */
    public function index(): View
    {
        /*
        |----------------------------------------------------------------------
        | SECTION A: Live Sales & POS Statistics (From 'sales' table)
        |----------------------------------------------------------------------
        */
        $completedSales = Sale::where('status', 'completed')->get();
        $todaySalesRecords = $completedSales->filter(fn (Sale $s) => $s->created_at && $s->created_at->isToday());
        $monthSalesRecords = $completedSales->filter(fn (Sale $s) => $s->created_at && $s->created_at->isCurrentMonth());

        $todaySalesTotal = (float) $todaySalesRecords->sum('grand_total');
        $monthlyRevenue = (float) $monthSalesRecords->sum('grand_total');
        $todayInvoicesCount = $todaySalesRecords->count();
        $avgBasketValue = $completedSales->count() > 0
            ? (float) ($completedSales->sum('grand_total') / $completedSales->count())
            : 0.00;

        $salesStats = [
            'today_sales' => $todaySalesTotal,
            'today_growth' => $todayInvoicesCount > 0 ? '+' . $todayInvoicesCount . ' today' : '0 today',
            'monthly_revenue' => $monthlyRevenue,
            'monthly_growth' => $completedSales->count() . ' total invoices',
            'today_invoices' => $todayInvoicesCount,
            'avg_basket_value' => $avgBasketValue,
            'active_pos_registers' => 1,
        ];

        /*
        |----------------------------------------------------------------------
        | SECTION B: Live Inventory, Stock & PO Statistics
        |----------------------------------------------------------------------
        */
        $products = Product::all();

        $inventoryStats = [
            'total_products' => $products->count(),
            'total_categories' => $products->pluck('category')->filter()->unique()->count(),
            'inventory_cost_value' => $products->sum(fn (Product $p) => (float) $p->cost_price * max(0, $p->stock_quantity)),
            'inventory_retail_value' => $products->sum(fn (Product $p) => (float) $p->selling_price * max(0, $p->stock_quantity)),
            'low_stock_count' => $products->filter(fn (Product $p) => $p->stock_quantity <= $p->min_stock_level)->count(),
            'out_of_stock_count' => $products->filter(fn (Product $p) => $p->stock_quantity <= 0)->count(),
            'pending_adjustments' => StockAdjustment::where('status', 'pending')->count(),
            'open_purchase_orders' => PurchaseOrder::where('status', 'ordered')->count(),
            'active_suppliers' => Supplier::where('status', 'active')->count(),
        ];

        /*
        |----------------------------------------------------------------------
        | SECTION C: Recent POS Sales / Invoices (From 'sales' table)
        |----------------------------------------------------------------------
        */
        $recentSales = Sale::with('cashier')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (Sale $sale) => [
                'invoice_no' => $sale->invoice_no,
                'cashier' => $sale->cashier?->name ?? 'System User',
                'items_count' => $sale->items_count,
                'payment_method' => $sale->payment_method,
                'total' => (float) $sale->grand_total,
                'status' => ucfirst($sale->status),
                'time' => $sale->created_at ? $sale->created_at->diffForHumans() : '',
            ])
            ->all();

        /*
        |----------------------------------------------------------------------
        | SECTION D: Low Stock Products Alert List (From 'products' table)
        |----------------------------------------------------------------------
        */
        $lowStockItems = $products
            ->filter(fn (Product $p) => $p->stock_quantity <= $p->min_stock_level)
            ->sortBy('stock_quantity')
            ->take(5)
            ->map(fn (Product $p) => [
                'sku' => $p->sku,
                'name' => $p->name,
                'category' => $p->category,
                'stock' => $p->stock_quantity,
                'min_level' => $p->min_stock_level,
                'cost_price' => (float) $p->cost_price,
                'status' => $p->stock_quantity <= 0
                    ? 'Out of Stock'
                    : ($p->stock_quantity <= max(1, (int) floor($p->min_stock_level / 2)) ? 'Critical' : 'Low Stock'),
            ])
            ->values()
            ->all();

        /*
        |----------------------------------------------------------------------
        | SECTION E: Pending Stock Adjustments & Open Purchase Orders
        |----------------------------------------------------------------------
        */
        $pendingAdjustmentsList = StockAdjustment::with(['requester', 'product'])
            ->where('status', 'pending')
            ->latest()
            ->take(3)
            ->get()
            ->map(fn (StockAdjustment $adj) => [
                'ref_no' => $adj->reference_no,
                'type' => 'Stock Adjustment',
                'requested_by' => $adj->requester?->name ?? 'Staff',
                'details' => ($adj->product?->name ?? 'Product') . ' (' . ($adj->adjustment_type === 'addition' ? '+' : '-') . $adj->quantity . ' units)',
                'status' => 'Pending Approval',
            ]);

        $openPoList = PurchaseOrder::with(['creator', 'supplier'])
            ->where('status', 'ordered')
            ->latest()
            ->take(3)
            ->get()
            ->map(fn (PurchaseOrder $po) => [
                'ref_no' => $po->po_number,
                'type' => 'Purchase Order',
                'requested_by' => $po->creator?->name ?? 'Staff',
                'details' => ($po->supplier?->company_name ?? 'Supplier') . ' (LKR ' . number_format((float) $po->total_amount, 0) . ')',
                'status' => 'Awaiting Delivery',
            ]);

        $pendingApprovals = $pendingAdjustmentsList->concat($openPoList)->take(5)->values()->all();

        /*
        |----------------------------------------------------------------------
        | SECTION F: System Users & Role Access Matrix (From 'users' table)
        |----------------------------------------------------------------------
        */
        $users = User::orderBy('id')->get();
        $totalUsers = $users->count();

        return view('admin.dashboard', [
            'salesStats' => $salesStats,
            'inventoryStats' => $inventoryStats,
            'recentSales' => $recentSales,
            'lowStockItems' => $lowStockItems,
            'pendingApprovals' => $pendingApprovals,
            'users' => $users,
            'totalUsers' => $totalUsers,
        ]);
    }
}
