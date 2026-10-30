<?php

/**
 * ============================================================================
 * FILE PATH: routes/web.php
 * DESCRIPTION: Main web routes connecting UI views and Backend Controllers.
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Auth Controller (Backend): app/Http/Controllers/Auth/LoginController.php
 * - Dashboard Controller (Backend): app/Http/Controllers/Admin/DashboardController.php
 * - Product Controller (CRUD Backend): app/Http/Controllers/Admin/ProductController.php
 * - Login View (UI): resources/views/auth/login.blade.php
 * - Admin Dashboard View (UI): resources/views/admin/dashboard.blade.php
 * - Products List View (UI): resources/views/admin/products/index.blade.php
 * - Add / Edit Product View (UI): resources/views/admin/products/form.blade.php
 * ============================================================================
 */

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockAdjustmentController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. Root Redirect Route
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| 2. Guest Routes (Only accessible when NOT logged in)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    Route::post('/forgot-password-request', [LoginController::class, 'requestPasswordReset'])->name('password.request.admin');
});

/*
|--------------------------------------------------------------------------
| 3. Authenticated ISM System Routes (Protected by 'auth' middleware)
|--------------------------------------------------------------------------
| - POST /logout                        -> Logs out user via LoginController@logout
| - GET /admin/dashboard                -> Module 1: Admin Dashboard UI (resources/views/admin/dashboard.blade.php)
| - GET /admin/products                 -> Module 2: Products List UI (resources/views/admin/products/index.blade.php)
| - GET /admin/products/create          -> Module 3: Add Product Page UI (resources/views/admin/products/form.blade.php)
| - GET /admin/products/{product}/edit  -> Module 3: Edit Product Page UI (resources/views/admin/products/form.blade.php)
| - POST /admin/products                -> Store New Product (ProductController@store)
| - PUT /admin/products/{product}       -> Update Existing Product (ProductController@update)
| - DELETE /admin/products/{product}    -> Delete Product (ProductController@destroy)
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Module 1: Dashboard
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Module 2: Products List
    Route::get('/admin/products', [ProductController::class, 'index'])->name('admin.products.index');

    // Module 3: Dedicated Add / Edit Product Page
    Route::get('/admin/products/create', [ProductController::class, 'create'])->name('admin.products.create');
    Route::get('/admin/products/{product}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');

    // Module 4: Dedicated Barcode / Label Print Page
    Route::get('/admin/products/barcodes', [ProductController::class, 'barcodes'])->name('admin.products.barcodes');

    // Backend Create / Update / Delete Actions
    Route::post('/admin/products', [ProductController::class, 'store'])->name('admin.products.store');
    Route::put('/admin/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/admin/products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');

    // Product Categories Directory (Admin, Store Manager, Stock Manager — Full CRUD)
    Route::get('/admin/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
    Route::post('/admin/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::put('/admin/categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/admin/categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');

    // Module 5: Stock Adjustments Page (Create, Approve, Reject, Delete)
    Route::get('/admin/stock-adjustments', [StockAdjustmentController::class, 'index'])->name('admin.stock-adjustments.index');
    Route::post('/admin/stock-adjustments', [StockAdjustmentController::class, 'store'])->name('admin.stock-adjustments.store');
    Route::patch('/admin/stock-adjustments/{stockAdjustment}/approve', [StockAdjustmentController::class, 'approve'])->name('admin.stock-adjustments.approve');
    Route::patch('/admin/stock-adjustments/{stockAdjustment}/reject', [StockAdjustmentController::class, 'reject'])->name('admin.stock-adjustments.reject');
    Route::delete('/admin/stock-adjustments/{stockAdjustment}', [StockAdjustmentController::class, 'destroy'])->name('admin.stock-adjustments.destroy');

    // Module 6: Suppliers Directory Page (Full CRUD)
    Route::get('/admin/suppliers', [SupplierController::class, 'index'])->name('admin.suppliers.index');
    Route::post('/admin/suppliers', [SupplierController::class, 'store'])->name('admin.suppliers.store');
    Route::put('/admin/suppliers/{supplier}', [SupplierController::class, 'update'])->name('admin.suppliers.update');
    Route::delete('/admin/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('admin.suppliers.destroy');

    // Module 7: Purchase Orders (POs) Page (Create, Receive Stock, Cancel, Delete)
    Route::get('/admin/purchase-orders', [PurchaseOrderController::class, 'index'])->name('admin.purchase-orders.index');
    Route::post('/admin/purchase-orders', [PurchaseOrderController::class, 'store'])->name('admin.purchase-orders.store');
    Route::patch('/admin/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('admin.purchase-orders.receive');
    Route::patch('/admin/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('admin.purchase-orders.cancel');
    Route::delete('/admin/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'destroy'])->name('admin.purchase-orders.destroy');

    // Module 8: POS Checkout Screen (Live Register & Checkout Action)
    Route::get('/admin/pos', [PosController::class, 'index'])->name('admin.pos.index');
    Route::post('/admin/pos/checkout', [PosController::class, 'checkout'])->name('admin.pos.checkout');

    // Module 9: Sales History / Invoices Page (View, Reprint Receipt, Refund, Delete)
    Route::get('/admin/sales', [SaleController::class, 'index'])->name('admin.sales.index');
    Route::patch('/admin/sales/{sale}/refund', [SaleController::class, 'refund'])->name('admin.sales.refund');
    Route::delete('/admin/sales/{sale}', [SaleController::class, 'destroy'])->name('admin.sales.destroy');

    // Module 10: Reports & Analytics Page
    Route::get('/admin/reports', [ReportController::class, 'index'])->name('admin.reports.index');

    // Module 11: User Management Page (Admin Only — Full CRUD & Role Assignment)
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

    // Module 12: System Settings Page (Admin Only — Store, Currency, Tax, Receipt & Security Settings)
    Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::put('/admin/settings', [SettingController::class, 'update'])->name('admin.settings.update');
    Route::post('/admin/settings/reset', [SettingController::class, 'reset'])->name('admin.settings.reset');
});
