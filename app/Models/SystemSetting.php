<?php

/**
 * ============================================================================
 * FILE PATH: app/Models/SystemSetting.php
 * DESCRIPTION: Eloquent Model for the 'system_settings' table (Module 12 — System Settings).
 *              Provides default store, POS, tax, receipt, and inventory configurations
 *              plus helper methods to read and persist settings cleanly.
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Database Migration: database/migrations/2026_10_29_000006_create_system_settings_table.php
 * - Setting Controller: app/Http/Controllers/Admin/SettingController.php
 * - System Settings UI: resources/views/admin/settings/index.blade.php
 * - Routes File: routes/web.php
 * ============================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'setting_key',
    'setting_value',
    'setting_group',
])]
class SystemSetting extends Model
{
    /**
     * PART 1: Default System Settings (Fallback & Initial Seed Values)
     *
     * @return array<string, array{value: string, group: string}>
     */
    public static function defaultDefinitions(): array
    {
        return [
            // 1. Store Profile & Contact Info
            'store_name' => ['value' => 'ISM Retail & POS Flagship Store', 'group' => 'store'],
            'store_code' => ['value' => 'BR-CMB-01', 'group' => 'store'],
            'store_tagline' => ['value' => 'Inventory & Sales Management System', 'group' => 'store'],
            'store_email' => ['value' => 'support@ism.com', 'group' => 'store'],
            'store_phone' => ['value' => '+94 11 234 5678', 'group' => 'store'],
            'store_address' => ['value' => 'No. 142, Galle Road, Colombo 03, Sri Lanka', 'group' => 'store'],
            'tax_registration_no' => ['value' => 'VAT-11489022-7000', 'group' => 'store'],

            // 2. Currency, Tax & Localization
            'currency_code' => ['value' => 'LKR', 'group' => 'financial'],
            'currency_symbol' => ['value' => 'LKR', 'group' => 'financial'],
            'default_tax_rate' => ['value' => '0.00', 'group' => 'financial'],
            'max_cashier_discount_pct' => ['value' => '15', 'group' => 'financial'],
            'timezone' => ['value' => 'Asia/Colombo', 'group' => 'financial'],
            'date_format' => ['value' => 'd M Y', 'group' => 'financial'],

            // 3. POS Register & Thermal Receipt Configuration
            'invoice_prefix' => ['value' => 'INV-2026-', 'group' => 'pos'],
            'po_prefix' => ['value' => 'PO-2026-', 'group' => 'pos'],
            'adjustment_prefix' => ['value' => 'ADJ-2026-', 'group' => 'pos'],
            'receipt_paper_size' => ['value' => '80mm', 'group' => 'pos'],
            'auto_print_receipt' => ['value' => '1', 'group' => 'pos'],
            'allow_negative_stock_pos' => ['value' => '0', 'group' => 'pos'],
            'receipt_header_note' => ['value' => 'Official Tax Invoice — Main Counter #01', 'group' => 'pos'],
            'receipt_footer_note' => ['value' => 'Thank you for shopping with ISM Store! Goods once sold can be exchanged within 7 days with original receipt.', 'group' => 'pos'],

            // 4. Inventory, Stock Alerts & Security Rules
            'default_min_stock_alert' => ['value' => '10', 'group' => 'inventory'],
            'require_adjustment_approval' => ['value' => '1', 'group' => 'inventory'],
            'hide_cost_price_from_cashier' => ['value' => '1', 'group' => 'inventory'],
            'restrict_cashier_own_shift_sales' => ['value' => '1', 'group' => 'inventory'],
            'session_timeout_minutes' => ['value' => '120', 'group' => 'inventory'],
        ];
    }

    /**
     * PART 2: Return all settings merged with defaults as a key => value array.
     *
     * @return array<string, string>
     */
    public static function getAllMerged(): array
    {
        $defaults = collect(self::defaultDefinitions())
            ->mapWithKeys(fn (array $item, string $key) => [$key => $item['value']])
            ->all();

        try {
            $stored = self::query()->pluck('setting_value', 'setting_key')->all();

            return array_merge($defaults, $stored);
        } catch (\Throwable) {
            return $defaults;
        }
    }

    /**
     * PART 3: Seed default settings if missing.
     */
    public static function ensureDefaultsExist(): void
    {
        foreach (self::defaultDefinitions() as $key => $meta) {
            self::firstOrCreate(
                ['setting_key' => $key],
                [
                    'setting_value' => $meta['value'],
                    'setting_group' => $meta['group'],
                ]
            );
        }
    }
}
