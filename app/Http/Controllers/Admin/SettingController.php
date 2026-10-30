<?php

/**
 * ============================================================================
 * FILE PATH: app/Http/Controllers/Admin/SettingController.php
 * DESCRIPTION: Module 12 — System Settings Controller (Admin Only).
 *              Manages Store Profile, Currency & Tax Rate, POS Thermal Receipt
 *              Header/Footer & Paper Size, Inventory Alert Thresholds, and
 *              Role Security Policies.
 * ============================================================================
 *
 * ROLE ACCESS MATRIX FOR THIS MODULE (Module 12: System Settings):
 * - Admin:         ✓ Full Access (View, Update, Reset to Defaults)
 * - Store Manager: ❌ No Access (403 Forbidden)
 * - Stock Manager: ❌ No Access (403 Forbidden)
 * - Cashier:       ❌ No Access (403 Forbidden)
 *
 * CONNECTED FILES:
 * - UI Blade View: resources/views/admin/settings/index.blade.php
 * - Master Layout: resources/views/layouts/admin.blade.php
 * - Shared Sidebar: resources/views/partials/sidebar.blade.php
 * - SystemSetting Model: app/Models/SystemSetting.php
 * - User Model (Role Check): app/Models/User.php
 * - Routes File: routes/web.php
 * ============================================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\StockAdjustment;
use App\Models\Supplier;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SettingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | PART 1: Display System Settings Page (GET /admin/settings)
    |--------------------------------------------------------------------------
    */
    public function index(): View
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->isAdmin()) {
            abort(403, 'Access denied. Only System Administrators can access System Settings.');
        }

        // Ensure default settings exist in the database
        SystemSetting::ensureDefaultsExist();

        $settings = SystemSetting::getAllMerged();

        // System health / database overview metrics for the System Status card
        $systemHealth = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_driver' => config('database.default'),
            'users_count' => User::count(),
            'products_count' => Product::count(),
            'suppliers_count' => Supplier::count(),
            'pos_sales_count' => Sale::count(),
            'purchase_orders_count' => PurchaseOrder::count(),
            'adjustments_count' => StockAdjustment::count(),
            'settings_count' => SystemSetting::count(),
        ];

        return view('admin.settings.index', [
            'settings' => $settings,
            'systemHealth' => $systemHealth,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PART 2: Save / Update System Settings (PUT /admin/settings)
    |--------------------------------------------------------------------------
    */
    public function update(Request $request): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->isAdmin()) {
            abort(403, 'Access denied. Only System Administrators can update System Settings.');
        }

        $validated = $request->validate([
            // 1. Store Profile
            'store_name' => ['required', 'string', 'max:150'],
            'store_code' => ['required', 'string', 'max:40'],
            'store_tagline' => ['nullable', 'string', 'max:180'],
            'store_email' => ['required', 'email', 'max:150'],
            'store_phone' => ['required', 'string', 'max:50'],
            'store_address' => ['required', 'string', 'max:255'],
            'tax_registration_no' => ['nullable', 'string', 'max:80'],

            // 2. Currency, Tax & Financial Rules
            'currency_code' => ['required', 'string', 'max:10'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'default_tax_rate' => ['required', 'numeric', 'min:0', 'max:50'],
            'max_cashier_discount_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'timezone' => ['required', 'string', 'max:60'],
            'date_format' => ['required', 'string', 'max:30'],

            // 3. POS Register & Thermal Receipt Settings
            'invoice_prefix' => ['required', 'string', 'max:30'],
            'po_prefix' => ['required', 'string', 'max:30'],
            'adjustment_prefix' => ['required', 'string', 'max:30'],
            'receipt_paper_size' => ['required', 'in:80mm,58mm,A4'],
            'receipt_header_note' => ['nullable', 'string', 'max:200'],
            'receipt_footer_note' => ['nullable', 'string', 'max:500'],

            // 4. Inventory & Security Controls
            'default_min_stock_alert' => ['required', 'integer', 'min:1', 'max:1000'],
            'session_timeout_minutes' => ['required', 'integer', 'min:15', 'max:1440'],
        ]);

        // Boolean toggle switches
        $booleanToggles = [
            'auto_print_receipt' => $request->boolean('auto_print_receipt') ? '1' : '0',
            'allow_negative_stock_pos' => $request->boolean('allow_negative_stock_pos') ? '1' : '0',
            'require_adjustment_approval' => $request->boolean('require_adjustment_approval') ? '1' : '0',
            'hide_cost_price_from_cashier' => $request->boolean('hide_cost_price_from_cashier') ? '1' : '0',
            'restrict_cashier_own_shift_sales' => $request->boolean('restrict_cashier_own_shift_sales') ? '1' : '0',
        ];

        $definitions = SystemSetting::defaultDefinitions();
        $payload = array_merge($validated, $booleanToggles);

        foreach ($payload as $key => $value) {
            $group = $definitions[$key]['group'] ?? 'general';
            SystemSetting::updateOrCreate(
                ['setting_key' => $key],
                [
                    'setting_value' => (string) ($value ?? ''),
                    'setting_group' => $group,
                ]
            );
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'System Settings have been saved and applied across the ISM System.');
    }

    /*
    |--------------------------------------------------------------------------
    | PART 3: Reset All Settings to Factory Defaults (POST /admin/settings/reset)
    |--------------------------------------------------------------------------
    */
    public function reset(): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->isAdmin()) {
            abort(403, 'Access denied. Only System Administrators can reset System Settings.');
        }

        foreach (SystemSetting::defaultDefinitions() as $key => $meta) {
            SystemSetting::updateOrCreate(
                ['setting_key' => $key],
                [
                    'setting_value' => $meta['value'],
                    'setting_group' => $meta['group'],
                ]
            );
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'All System Settings have been restored to factory defaults.');
    }
}
