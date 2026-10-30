<?php

/**
 * ============================================================================
 * FILE PATH: app/Providers/AppServiceProvider.php
 * DESCRIPTION: Shares the global '$systemSettings' array (from 'system_settings'
 *              table via SystemSetting::getAllMerged()) with ALL Blade views
 *              so Store Name, Address, Phone, Email, Tax Reg No, Currency,
 *              and Receipt Header/Footer are dynamically displayed everywhere.
 * ============================================================================
 *
 * CONNECTED FILES:
 * - SystemSetting Model: app/Models/SystemSetting.php
 * - System Settings Controller: app/Http/Controllers/Admin/SettingController.php
 * - POS Receipt View: resources/views/admin/pos/index.blade.php
 * - Sales Invoices View: resources/views/admin/sales/index.blade.php
 * - Reports & Analytics View: resources/views/admin/reports/index.blade.php
 * - Barcode Label Print View: resources/views/admin/products/barcodes.blade.php
 * - Shared Sidebar & Footer: resources/views/partials/sidebar.blade.php & footer.blade.php
 * ============================================================================
 */

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share '$systemSettings' dynamically with every Blade view in the application
        View::composer('*', function ($view) {
            static $cachedSettings = null;

            if ($cachedSettings === null) {
                $cachedSettings = SystemSetting::getAllMerged();
            }

            $view->with('systemSettings', $cachedSettings);
        });
    }
}
