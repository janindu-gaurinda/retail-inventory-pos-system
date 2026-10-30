<?php

/**
 * ============================================================================
 * FILE PATH: app/Http/Controllers/Admin/ReportController.php
 * DESCRIPTION: Backend Controller for Module 10 — Reports & Analytics.
 *              Aggregates real database analytics across:
 *              - Sales Revenue, Gross Profit & Payment Method Breakdown
 *              - Top-Selling Products & Cashier Performance
 *              - Inventory Stock Valuation by Category (Cost vs Retail)
 *              - Purchasing (POs) & Stock Adjustments Summary
 *              - ROLE PROTECTION: Admin & Store Manager (✓); Stock Manager & Cashier (❌ 403)
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Routes File: routes/web.php (Route: GET /admin/reports -> 'admin.reports.index')
 * - Models Used:
 *   - app/Models/Sale.php & app/Models/SaleItem.php
 *   - app/Models/Product.php
 *   - app/Models/PurchaseOrder.php
 *   - app/Models/StockAdjustment.php
 *   - app/Models/Supplier.php
 * - Reports & Analytics UI: resources/views/admin/reports/index.blade.php
 * ============================================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockAdjustment;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * PART 1: Display the Reports & Analytics Page UI with Live Database Aggregations.
     *
     * Route: GET /admin/reports (route name: 'admin.reports.index')
     * UI View Path: resources/views/admin/reports/index.blade.php
     */
    public function index(Request $request): View
    {
        if (! $request->user()->canAccessReports()) {
            abort(403, 'Unauthorized: Only Admin and Store Manager can access Reports & Analytics.');
        }

        /*
        |----------------------------------------------------------------------
        | SECTION A: Sales, Revenue & Estimated Profit Analytics
        |----------------------------------------------------------------------
        */
        $completedSales = Sale::with(['items.product', 'cashier'])
            ->where('status', 'completed')
            ->get();

        $totalSalesRevenue = $completedSales->sum(fn (Sale $s) => (float) $s->grand_total);
        $totalDiscountGiven = $completedSales->sum(fn (Sale $s) => (float) $s->discount_amount);
        $totalInvoicesCount = $completedSales->count();
        $totalUnitsSold = $completedSales->sum('items_count');

        // Calculate Estimated Cost of Goods Sold (COGS) & Gross Profit
        $totalCogs = 0.0;
        foreach ($completedSales as $sale) {
            foreach ($sale->items as $item) {
                $unitCost = $item->product ? (float) $item->product->cost_price : ((float) $item->unit_price * 0.65);
                $totalCogs += ($unitCost * $item->quantity);
            }
        }
        $grossProfit = max(0, $totalSalesRevenue - $totalCogs);
        $profitMarginPct = $totalSalesRevenue > 0 ? round(($grossProfit / $totalSalesRevenue) * 100, 1) : 0.0;

        /*
        |----------------------------------------------------------------------
        | SECTION B: Sales Breakdown by Payment Method (Cash, Card, QR / Bank)
        |----------------------------------------------------------------------
        */
        $paymentBreakdown = collect(['Cash', 'Card', 'QR / Bank'])->map(function (string $method) use ($completedSales, $totalSalesRevenue) {
            $subset = $completedSales->where('payment_method', $method);
            $amount = $subset->sum(fn (Sale $s) => (float) $s->grand_total);
            $share = $totalSalesRevenue > 0 ? round(($amount / $totalSalesRevenue) * 100, 1) : 0.0;

            return [
                'method' => $method,
                'count' => $subset->count(),
                'amount' => $amount,
                'share' => $share,
            ];
        });

        /*
        |----------------------------------------------------------------------
        | SECTION C: Top-Selling Products Report
        |----------------------------------------------------------------------
        */
        $completedSaleIds = $completedSales->pluck('id');
        $topProducts = SaleItem::whereIn('sale_id', $completedSaleIds)
            ->get()
            ->groupBy('sku')
            ->map(function ($items, $sku) {
                $first = $items->first();

                return [
                    'sku' => $sku,
                    'product_name' => $first->product_name,
                    'qty_sold' => $items->sum('quantity'),
                    'revenue' => $items->sum(fn ($i) => (float) $i->line_total),
                ];
            })
            ->sortByDesc('revenue')
            ->values();

        /*
        |----------------------------------------------------------------------
        | SECTION D: Inventory Valuation & Category Stock Report
        |----------------------------------------------------------------------
        */
        $allProducts = Product::all();
        $inventoryReport = [
            'total_skus' => $allProducts->count(),
            'total_units_in_stock' => $allProducts->sum('stock_quantity'),
            'total_cost_valuation' => $allProducts->sum(fn (Product $p) => (float) $p->cost_price * max(0, $p->stock_quantity)),
            'total_retail_valuation' => $allProducts->sum(fn (Product $p) => (float) $p->selling_price * max(0, $p->stock_quantity)),
            'low_stock_count' => $allProducts->filter(fn (Product $p) => $p->stock_quantity > 0 && $p->stock_quantity <= $p->min_stock_level)->count(),
            'out_of_stock_count' => $allProducts->filter(fn (Product $p) => $p->stock_quantity <= 0)->count(),
        ];
        $inventoryReport['projected_stock_profit'] = max(0, $inventoryReport['total_retail_valuation'] - $inventoryReport['total_cost_valuation']);

        // Category-wise breakdown
        $categoryBreakdown = $allProducts->groupBy('category')->map(function ($items, $category) {
            $costVal = $items->sum(fn (Product $p) => (float) $p->cost_price * max(0, $p->stock_quantity));
            $retailVal = $items->sum(fn (Product $p) => (float) $p->selling_price * max(0, $p->stock_quantity));

            return [
                'category' => $category,
                'products_count' => $items->count(),
                'units_in_stock' => $items->sum('stock_quantity'),
                'cost_value' => $costVal,
                'retail_value' => $retailVal,
                'expected_profit' => max(0, $retailVal - $costVal),
            ];
        })->sortByDesc('retail_value')->values();

        /*
        |----------------------------------------------------------------------
        | SECTION E: Purchasing (POs), Suppliers & Stock Adjustments Summary
        |----------------------------------------------------------------------
        */
        $allPOs = PurchaseOrder::all();
        $allAdjustments = StockAdjustment::all();
        $allSuppliers = Supplier::all();

        $operationsSummary = [
            'open_pos_count' => $allPOs->where('status', 'ordered')->count(),
            'open_pos_value' => $allPOs->where('status', 'ordered')->sum(fn (PurchaseOrder $po) => (float) $po->total_amount),
            'received_pos_count' => $allPOs->where('status', 'received')->count(),
            'received_pos_value' => $allPOs->where('status', 'received')->sum(fn (PurchaseOrder $po) => (float) $po->total_amount),
            'total_supplier_payables' => $allSuppliers->sum(fn (Supplier $s) => (float) $s->outstanding_balance),
            'approved_additions' => $allAdjustments->where('status', 'approved')->where('adjustment_type', 'addition')->sum('quantity'),
            'approved_subtractions' => $allAdjustments->where('status', 'approved')->where('adjustment_type', 'subtraction')->sum('quantity'),
            'pending_adjustments' => $allAdjustments->where('status', 'pending')->count(),
        ];

        return view('admin.reports.index', [
            'salesKpi' => [
                'revenue' => $totalSalesRevenue,
                'cogs' => $totalCogs,
                'gross_profit' => $grossProfit,
                'profit_margin' => $profitMarginPct,
                'discounts' => $totalDiscountGiven,
                'invoices_count' => $totalInvoicesCount,
                'units_sold' => $totalUnitsSold,
            ],
            'paymentBreakdown' => $paymentBreakdown,
            'topProducts' => $topProducts,
            'inventoryReport' => $inventoryReport,
            'categoryBreakdown' => $categoryBreakdown,
            'operationsSummary' => $operationsSummary,
        ]);
    }
}
