<?php

/**
 * ============================================================================
 * FILE PATH: app/Http/Controllers/Admin/SaleController.php
 * DESCRIPTION: Backend Controller for Module 9 — Sales History / Invoices.
 *              Implements Role-Based Permissions from the ISM Matrix:
 *              - Admin: ✓ View All Shifts & Cashiers + Refund & Restore Stock + Delete
 *              - Store Manager: ✓ View All Shifts & Cashiers + Refund & Restore Stock
 *              - Cashier: ✓ View Own Shift / Own Invoices Only
 *              - Stock Manager: ❌ Blocked (403)
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Routes File: routes/web.php (Routes: 'admin.sales.*')
 * - Sale Model (Database): app/Models/Sale.php
 * - SaleItem Model (Database): app/Models/SaleItem.php
 * - Product Model (Stock Restoration on Refund): app/Models/Product.php
 * - User Model (Permissions): app/Models/User.php
 * - Sales History UI: resources/views/admin/sales/index.blade.php
 * ============================================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SaleController extends Controller
{
    /**
     * PART 1: Display the Sales History / Invoices Page UI with Filters & KPIs.
     *
     * Route: GET /admin/sales (route name: 'admin.sales.index')
     * UI View Path: resources/views/admin/sales/index.blade.php
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if (! $user->canAccessSalesHistory()) {
            abort(403, 'Unauthorized: Stock Manager role cannot access Sales History / Invoices.');
        }

        $search = trim((string) $request->query('search', ''));
        $paymentFilter = trim((string) $request->query('payment_method', ''));
        $statusFilter = trim((string) $request->query('status', ''));
        $cashierFilter = trim((string) $request->query('cashier_id', ''));

        $query = Sale::with(['items', 'cashier']);

        // Role Check: Cashiers can ONLY see their own shift/account invoices
        if (! $user->canViewAllSales()) {
            $query->where('user_id', $user->id);
        } elseif ($cashierFilter !== '') {
            $query->where('user_id', $cashierFilter);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        if ($paymentFilter !== '') {
            $query->where('payment_method', $paymentFilter);
        }

        if ($statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        $sales = $query->orderByDesc('id')->get();

        // Scope KPIs according to role (All Invoices for Admin/Store Manager, Own Shift for Cashier)
        $kpiQuery = Sale::query();
        if (! $user->canViewAllSales()) {
            $kpiQuery->where('user_id', $user->id);
        }
        $allScopedSales = $kpiQuery->get();

        $completedSales = $allScopedSales->where('status', 'completed');
        $summary = [
            'total_invoices' => $allScopedSales->count(),
            'completed_revenue' => $completedSales->sum(fn (Sale $s) => (float) $s->grand_total),
            'total_items_sold' => $completedSales->sum('items_count'),
            'refunded_count' => $allScopedSales->where('status', 'refunded')->count(),
            'refunded_amount' => $allScopedSales->where('status', 'refunded')->sum(fn (Sale $s) => (float) $s->grand_total),
        ];

        $cashiers = User::whereIn('role', [
            User::ROLE_ADMIN,
            'super_admin',
            User::ROLE_STORE_MANAGER,
            User::ROLE_CASHIER,
        ])->orderBy('name')->get();

        return view('admin.sales.index', [
            'sales' => $sales,
            'summary' => $summary,
            'cashiers' => $cashiers,
            'filters' => [
                'search' => $search,
                'payment_method' => $paymentFilter,
                'status' => $statusFilter,
                'cashier_id' => $cashierFilter,
            ],
        ]);
    }

    /**
     * PART 2: Refund an Invoice & Automatically Restore Sold Quantities to Stock.
     *         Allowed Roles: Admin & Store Manager.
     *
     * Route: PATCH /admin/sales/{sale}/refund (route name: 'admin.sales.refund')
     */
    public function refund(Request $request, Sale $sale): RedirectResponse
    {
        if (! $request->user()->canViewAllSales()) {
            abort(403, 'Unauthorized: Only Admin and Store Manager can process invoice refunds.');
        }

        if ($sale->status === 'refunded') {
            return back()->with('success', "Invoice {$sale->invoice_no} is already refunded.");
        }

        DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                if ($item->product_id) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->update([
                            'stock_quantity' => $product->stock_quantity + $item->quantity,
                        ]);
                    }
                }
            }

            $sale->update(['status' => 'refunded']);
        });

        return redirect()
            ->route('admin.sales.index')
            ->with('success', "Invoice {$sale->invoice_no} marked as Refunded and item quantities restored to inventory.");
    }

    /**
     * PART 3: Delete an Invoice Record (Admin / Store Manager).
     *
     * Route: DELETE /admin/sales/{sale} (route name: 'admin.sales.destroy')
     */
    public function destroy(Request $request, Sale $sale): RedirectResponse
    {
        if (! $request->user()->canViewAllSales()) {
            abort(403, 'Unauthorized action.');
        }

        $invNo = $sale->invoice_no;
        $sale->delete();

        return redirect()
            ->route('admin.sales.index')
            ->with('success', "Invoice {$invNo} deleted from sales history.");
    }
}
