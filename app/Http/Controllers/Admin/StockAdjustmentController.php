<?php

/**
 * ============================================================================
 * FILE PATH: app/Http/Controllers/Admin/StockAdjustmentController.php
 * DESCRIPTION: Backend Controller for Module 5 — Stock Adjustments.
 *              Implements Role-Based Permissions from the ISM Matrix:
 *              - Admin: Full Access (Create, Approve, Reject, Delete)
 *              - Store Manager: Approve / Reject Stock Adjustments
 *              - Stock Manager: Create Stock Adjustment Requests
 *              - Cashier: Blocked (403)
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Routes File: routes/web.php (Routes: 'admin.stock-adjustments.*')
 * - StockAdjustment Model (Database): app/Models/StockAdjustment.php
 * - Product Model (Stock Updates): app/Models/Product.php
 * - User Model (Permissions): app/Models/User.php
 * - Stock Adjustments UI: resources/views/admin/stock_adjustments/index.blade.php
 * ============================================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockAdjustmentController extends Controller
{
    /**
     * PART 1: Display the Stock Adjustments Page UI with Filters & KPIs.
     *
     * Route: GET /admin/stock-adjustments (route name: 'admin.stock-adjustments.index')
     * UI View Path: resources/views/admin/stock_adjustments/index.blade.php
     */
    public function index(Request $request): View
    {
        if (! $request->user()->canAccessStockAdjustments()) {
            abort(403, 'Unauthorized: Cashier role cannot access Stock Adjustments.');
        }

        $search = trim((string) $request->query('search', ''));
        $statusFilter = trim((string) $request->query('status', ''));
        $typeFilter = trim((string) $request->query('type', ''));

        $query = StockAdjustment::with(['product', 'requester', 'approver']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    });
            });
        }

        if ($statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        if ($typeFilter !== '') {
            $query->where('adjustment_type', $typeFilter);
        }

        $adjustments = $query->orderByDesc('id')->get();

        // Calculate Summary KPIs across all stock adjustments
        $allAdjustments = StockAdjustment::all();
        $summary = [
            'total_count' => $allAdjustments->count(),
            'pending_count' => $allAdjustments->where('status', 'pending')->count(),
            'approved_count' => $allAdjustments->where('status', 'approved')->count(),
            'rejected_count' => $allAdjustments->where('status', 'rejected')->count(),
        ];

        $products = Product::orderBy('name')->get();

        return view('admin.stock_adjustments.index', [
            'adjustments' => $adjustments,
            'summary' => $summary,
            'products' => $products,
            'filters' => [
                'search' => $search,
                'status' => $statusFilter,
                'type' => $typeFilter,
            ],
        ]);
    }

    /**
     * PART 2: Create a New Stock Adjustment Request (CREATE).
     *         Allowed Roles: Admin & Stock Manager.
     *         If Admin creates & chooses "Auto-Approve", product stock updates immediately.
     *         Otherwise it is saved as 'pending' for Admin / Store Manager approval.
     *
     * Route: POST /admin/stock-adjustments (route name: 'admin.stock-adjustments.store')
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->canCreateStockAdjustment()) {
            abort(403, 'Unauthorized: Only Admin and Stock Manager can create Stock Adjustments.');
        }

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'adjustment_type' => ['required', Rule::in(['addition', 'subtraction'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $previousStock = $product->stock_quantity;
        $qty = (int) $validated['quantity'];

        $newStock = $validated['adjustment_type'] === 'addition'
            ? $previousStock + $qty
            : max(0, $previousStock - $qty);

        // Generate unique Reference Number (using adjustment_prefix from SystemSettings)
        $nextNumber = StockAdjustment::max('id') + 1;
        $adjPrefix = \App\Models\SystemSetting::getAllMerged()['adjustment_prefix'] ?? ('ADJ-' . date('Y') . '-');
        $refNo = $adjPrefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);

        // Admin can optionally auto-approve immediately upon creation
        $autoApprove = $user->canApproveStockAdjustment() && $request->boolean('auto_approve');

        $adjustment = StockAdjustment::create([
            'reference_no' => $refNo,
            'product_id' => $product->id,
            'adjustment_type' => $validated['adjustment_type'],
            'quantity' => $qty,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'reason' => $validated['reason'],
            'notes' => $validated['notes'] ?? null,
            'status' => $autoApprove ? 'approved' : 'pending',
            'requested_by' => $user->id,
            'approved_by' => $autoApprove ? $user->id : null,
            'approved_at' => $autoApprove ? now() : null,
        ]);

        if ($autoApprove) {
            $product->update(['stock_quantity' => $newStock]);

            return redirect()
                ->route('admin.stock-adjustments.index')
                ->with('success', "Stock Adjustment {$adjustment->reference_no} created and immediately approved. Stock updated to {$newStock}.");
        }

        return redirect()
            ->route('admin.stock-adjustments.index')
            ->with('success', "Stock Adjustment {$adjustment->reference_no} submitted for approval.");
    }

    /**
     * PART 3: Approve a Pending Stock Adjustment & Update Product Stock (APPROVE).
     *         Allowed Roles: Admin & Store Manager.
     *
     * Route: PATCH /admin/stock-adjustments/{stockAdjustment}/approve
     */
    public function approve(Request $request, StockAdjustment $stockAdjustment): RedirectResponse
    {
        $user = $request->user();

        if (! $user->canApproveStockAdjustment()) {
            abort(403, 'Unauthorized: Only Admin and Store Manager can approve Stock Adjustments.');
        }

        if ($stockAdjustment->status !== 'pending') {
            return back()->with('success', "Adjustment {$stockAdjustment->reference_no} has already been processed.");
        }

        $product = $stockAdjustment->product;
        $previousStock = $product->stock_quantity;
        $newStock = $stockAdjustment->adjustment_type === 'addition'
            ? $previousStock + $stockAdjustment->quantity
            : max(0, $previousStock - $stockAdjustment->quantity);

        // Update product's actual stock quantity
        $product->update(['stock_quantity' => $newStock]);

        // Mark adjustment as approved
        $stockAdjustment->update([
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('admin.stock-adjustments.index')
            ->with('success', "Adjustment {$stockAdjustment->reference_no} approved! '{$product->name}' stock updated ({$previousStock} → {$newStock}).");
    }

    /**
     * PART 4: Reject a Pending Stock Adjustment Request (REJECT).
     *         Allowed Roles: Admin & Store Manager.
     *
     * Route: PATCH /admin/stock-adjustments/{stockAdjustment}/reject
     */
    public function reject(Request $request, StockAdjustment $stockAdjustment): RedirectResponse
    {
        $user = $request->user();

        if (! $user->canApproveStockAdjustment()) {
            abort(403, 'Unauthorized: Only Admin and Store Manager can reject Stock Adjustments.');
        }

        if ($stockAdjustment->status !== 'pending') {
            return back()->with('success', "Adjustment {$stockAdjustment->reference_no} has already been processed.");
        }

        $stockAdjustment->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('admin.stock-adjustments.index')
            ->with('success', "Adjustment {$stockAdjustment->reference_no} has been rejected.");
    }

    /**
     * PART 5: Delete a Stock Adjustment Record (DELETE).
     *         Allowed Roles: Admin.
     *
     * Route: DELETE /admin/stock-adjustments/{stockAdjustment}
     */
    public function destroy(Request $request, StockAdjustment $stockAdjustment): RedirectResponse
    {
        if (! $request->user()->canApproveStockAdjustment()) {
            abort(403, 'Unauthorized action.');
        }

        $refNo = $stockAdjustment->reference_no;
        $stockAdjustment->delete();

        return redirect()
            ->route('admin.stock-adjustments.index')
            ->with('success', "Adjustment record {$refNo} deleted.");
    }
}
