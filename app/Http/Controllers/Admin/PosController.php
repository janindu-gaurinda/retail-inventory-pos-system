<?php

/**
 * ============================================================================
 * FILE PATH: app/Http/Controllers/Admin/PosController.php
 * DESCRIPTION: Backend Controller for Module 8 — POS Checkout Screen.
 *              Supports:
 *              - Live Product Catalog Grid & Barcode Scanner Lookup
 *              - Cart Checkout Processing (Creates Sale + SaleItems)
 *              - Automatic Real-Time Product Stock Deduction
 *              - Printable Thermal Receipt Preview after Checkout
 *              - ROLE PROTECTION: Admin, Store Manager, Cashier (✓); Stock Manager (❌ 403)
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Routes File: routes/web.php (Routes: 'admin.pos.index', 'admin.pos.checkout')
 * - Sale Model (Database): app/Models/Sale.php
 * - SaleItem Model (Database): app/Models/SaleItem.php
 * - Product Model (Stock Deduction): app/Models/Product.php
 * - User Model (Permissions): app/Models/User.php
 * - POS Checkout UI: resources/views/admin/pos/index.blade.php
 * ============================================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PosController extends Controller
{
    /**
     * PART 1: Display the POS Checkout Screen UI.
     *
     * Route: GET /admin/pos (route name: 'admin.pos.index')
     * UI View Path: resources/views/admin/pos/index.blade.php
     */
    public function index(Request $request): View
    {
        if (! $request->user()->canAccessPos()) {
            abort(403, 'Unauthorized: Stock Manager role cannot access the POS Checkout Screen.');
        }

        $products = Product::where('status', 'active')
            ->orderBy('name')
            ->get();

        $categories = $products->pluck('category')->unique()->sort()->values();

        // Recent completed sales for quick receipt reprint
        $recentSales = Sale::with(['items', 'cashier'])
            ->orderByDesc('id')
            ->take(5)
            ->get();

        // Check if a sale was just completed to auto-open the receipt modal
        $lastCompletedSale = null;
        if (session('last_sale_id')) {
            $lastCompletedSale = Sale::with(['items', 'cashier'])->find(session('last_sale_id'));
        }

        // Distinct customer/channel names from previous sales merged with standard POS channels
        $customerOptions = collect([
            'Walk-in Customer (In-Store)',
            'Online Order (Website / Social)',
            'Wholesale / Trade Buyer',
            'Phone / WhatsApp Delivery Order',
            'Corporate Account Customer',
        ])->merge(Sale::pluck('customer_name'))
            ->filter()
            ->unique()
            ->values();

        return view('admin.pos.index', [
            'products' => $products,
            'categories' => $categories,
            'recentSales' => $recentSales,
            'lastCompletedSale' => $lastCompletedSale,
            'customerOptions' => $customerOptions,
        ]);
    }

    /**
     * PART 2: Process POS Checkout & Deduct Inventory Stock (CREATE SALE).
     *
     * Route: POST /admin/pos/checkout (route name: 'admin.pos.checkout')
     * Form Source: POS Cart Checkout Form in resources/views/admin/pos/index.blade.php
     */
    public function checkout(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->canAccessPos()) {
            abort(403, 'Unauthorized: Stock Manager role cannot process POS sales.');
        }

        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:150'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'payment_method' => ['required', Rule::in(['Cash', 'Card', 'QR / Bank'])],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'cart_json' => ['required', 'string'],
        ]);

        $cartItems = json_decode($validated['cart_json'], true);

        if (! is_array($cartItems) || count($cartItems) === 0) {
            return back()->withErrors(['cart' => 'Cannot complete checkout with an empty cart.']);
        }

        return DB::transaction(function () use ($cartItems, $validated, $user) {
            $subtotal = 0.0;
            $totalItemsCount = 0;
            $preparedLineItems = [];

            foreach ($cartItems as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['id']);
                $qty = max(1, (int) ($item['qty'] ?? 1));

                if ($product->stock_quantity < $qty) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'stock' => "Insufficient stock for '{$product->name}' (Available: {$product->stock_quantity}, Requested: {$qty}).",
                    ]);
                }

                $unitPrice = (float) $product->selling_price;
                $lineTotal = round($unitPrice * $qty, 2);

                $subtotal += $lineTotal;
                $totalItemsCount += $qty;

                // Deduct stock from product inventory
                $product->update([
                    'stock_quantity' => $product->stock_quantity - $qty,
                ]);

                $preparedLineItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            $discount = min($subtotal, (float) ($validated['discount_amount'] ?? 0));
            $taxRate = (float) ($validated['tax_rate'] ?? 0);
            $taxableAmount = max(0, $subtotal - $discount);
            $taxAmount = round($taxableAmount * ($taxRate / 100), 2);
            $grandTotal = round($taxableAmount + $taxAmount, 2);

            $paidAmount = max($grandTotal, (float) $validated['paid_amount']);
            $changeAmount = round($paidAmount - $grandTotal, 2);

            $nextId = Sale::max('id') + 1;
            $invoicePrefix = \App\Models\SystemSetting::getAllMerged()['invoice_prefix'] ?? ('INV-' . date('Y') . '-');
            $invoiceNo = $invoicePrefix . str_pad((string) (1000 + $nextId), 4, '0', STR_PAD_LEFT);

            $sale = Sale::create([
                'invoice_no' => $invoiceNo,
                'user_id' => $user->id,
                'customer_name' => trim($validated['customer_name'] ?? '') ?: 'Walk-in Customer',
                'customer_phone' => $validated['customer_phone'] ?? null,
                'items_count' => $totalItemsCount,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $validated['payment_method'],
                'status' => 'completed',
            ]);

            foreach ($preparedLineItems as $line) {
                SaleItem::create(array_merge($line, ['sale_id' => $sale->id]));
            }

            return redirect()
                ->route('admin.pos.index')
                ->with('success', "Sale {$sale->invoice_no} completed! Total: LKR " . number_format($grandTotal, 2) . " | Change Due: LKR " . number_format($changeAmount, 2))
                ->with('last_sale_id', $sale->id);
        });
    }
}
