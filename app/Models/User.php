<?php

/**
 * ============================================================================
 * FILE PATH: app/Models/User.php
 * DESCRIPTION: Eloquent User Model representing the 'users' database table
 *              with Role-Based Access Control (Admin, Store Manager, Stock Manager, Cashier).
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Database Migration: database/migrations/0001_01_01_000000_create_users_table.php
 * - Database Seeder: database/seeders/DatabaseSeeder.php
 * - Auth Controller: app/Http/Controllers/Auth/LoginController.php
 * - Dashboard Controller: app/Http/Controllers/Admin/DashboardController.php
 * - Product Controller: app/Http/Controllers/Admin/ProductController.php
 * - StockAdjustment Controller: app/Http/Controllers/Admin/StockAdjustmentController.php
 * ============================================================================
 */

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'role', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /*
    |--------------------------------------------------------------------------
    | PART 1: System Role Constants
    |--------------------------------------------------------------------------
    */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_STORE_MANAGER = 'store_manager';
    public const ROLE_STOCK_MANAGER = 'stock_manager';
    public const ROLE_CASHIER = 'cashier';

    /**
     * PART 2: Human-readable role badge label for UI views.
     */
    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN, 'super_admin' => 'Admin',
            self::ROLE_STORE_MANAGER => 'Store Manager',
            self::ROLE_STOCK_MANAGER => 'Stock Manager',
            self::ROLE_CASHIER => 'Cashier',
            default => ucfirst(str_replace('_', ' ', (string) $this->role)),
        };
    }

    /**
     * PART 3: Role Permission Helpers for Products Module
     * - Admin, Store Manager, Stock Manager: View & Edit (Full CRUD + Cost Price)
     * - Cashier: View Only (No Cost Price, No Create/Update/Delete)
     */
    public function canManageProducts(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            'super_admin',
            self::ROLE_STORE_MANAGER,
            self::ROLE_STOCK_MANAGER,
        ], true);
    }

    public function canViewCostPrice(): bool
    {
        return $this->canManageProducts();
    }

    /**
     * PART 4: Role Permission Helpers for Stock Adjustments Module (Module 5)
     * Matrix Specification:
     * - Admin: ✓ (Full — Create & Approve)
     * - Store Manager: ✓ (Approve)
     * - Stock Manager: ✓ (Create)
     * - Cashier: ❌ (No Access)
     */
    public function canAccessStockAdjustments(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            'super_admin',
            self::ROLE_STORE_MANAGER,
            self::ROLE_STOCK_MANAGER,
        ], true);
    }

    public function canCreateStockAdjustment(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            'super_admin',
            self::ROLE_STOCK_MANAGER,
        ], true);
    }

    public function canApproveStockAdjustment(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            'super_admin',
            self::ROLE_STORE_MANAGER,
        ], true);
    }

    /**
     * PART 5: Role Permission Helper for Categories & Suppliers Directory
     * Matrix Specification:
     * - Admin (Owner / IT):               ✓ Full Access (Create, Edit, Delete)
     * - Store Manager:                    ✓ Full Access (Create, Edit, Delete)
     * - Stock Manager (Inventory Clerk):  ✓ Full Access (Create, Edit, Delete)
     * - Cashier:                          ❌ No Access
     */
    public function canAccessSuppliers(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            'super_admin',
            self::ROLE_STORE_MANAGER,
            self::ROLE_STOCK_MANAGER,
        ], true);
    }

    public function canManageCategories(): bool
    {
        return $this->canAccessSuppliers();
    }

    /**
     * PART 6: Role Permission Helper for Purchase Orders Module (Module 7)
     * Matrix Specification:
     * - Admin: ✓ (Full Access)
     * - Store Manager: ✓ (Full Access)
     * - Stock Manager: ✓ (Create & Receive)
     * - Cashier: ❌ (No Access)
     */
    public function canAccessPurchaseOrders(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            'super_admin',
            self::ROLE_STORE_MANAGER,
            self::ROLE_STOCK_MANAGER,
        ], true);
    }

    /**
     * PART 7: Role Permission Helper for POS Checkout Screen (Module 8)
     * Matrix Specification:
     * - Admin: ✓
     * - Store Manager: ✓
     * - Stock Manager: ❌ (No Access)
     * - Cashier: ✓
     */
    public function canAccessPos(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            'super_admin',
            self::ROLE_STORE_MANAGER,
            self::ROLE_CASHIER,
        ], true);
    }

    /**
     * PART 8: Role Permission Helpers for Sales History / Invoices (Module 9)
     * Matrix Specification:
     * - Admin: ✓ (All Shifts & All Cashiers + Refund/Delete)
     * - Store Manager: ✓ (All Shifts & All Cashiers + Refund)
     * - Stock Manager: ❌ (No Access)
     * - Cashier: ✓ (Own Shift Only)
     */
    public function canAccessSalesHistory(): bool
    {
        return $this->canAccessPos();
    }

    public function canViewAllSales(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            'super_admin',
            self::ROLE_STORE_MANAGER,
        ], true);
    }

    /**
     * PART 9: Role Permission Helper for Reports & Analytics (Module 10)
     * Matrix Specification:
     * - Admin: ✓ (Full Sales, Profit & Inventory Reports)
     * - Store Manager: ✓ (Sales / Stock Reports)
     * - Stock Manager: ❌ (No Access)
     * - Cashier: ❌ (No Access)
     */
    public function canAccessReports(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            'super_admin',
            self::ROLE_STORE_MANAGER,
        ], true);
    }

    /**
     * PART 10: Role Permission Helper for User Management & System Settings (Modules 11 & 12)
     * Matrix Specification:
     * - Admin: ✓ (Full Access)
     * - Store Manager: ❌ (No Access)
     * - Stock Manager: ❌ (No Access)
     * - Cashier: ❌ (No Access)
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            'super_admin',
        ], true);
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * PART 5: Attribute type casting for database fields.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
