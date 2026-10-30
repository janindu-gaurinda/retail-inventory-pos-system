<?php

/**
 * ============================================================================
 * FILE PATH: app/Models/Supplier.php
 * DESCRIPTION: Eloquent Model for the 'suppliers' table (Module 6 — Suppliers Directory).
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Database Migration: database/migrations/2026_10_29_000003_create_suppliers_table.php
 * - Supplier Controller (CRUD Backend): app/Http/Controllers/Admin/SupplierController.php
 * - Database Seeder: database/seeders/DatabaseSeeder.php
 * - Suppliers Directory UI: resources/views/admin/suppliers/index.blade.php
 * ============================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'supplier_code',
    'company_name',
    'contact_person',
    'email',
    'phone',
    'category',
    'city',
    'address',
    'tax_number',
    'payment_terms',
    'outstanding_balance',
    'status',
])]
class Supplier extends Model
{
    /**
     * PART 1: Attribute type casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'outstanding_balance' => 'decimal:2',
        ];
    }

    /**
     * PART 2: Count of inventory products supplied by this company.
     */
    public function productsCount(): int
    {
        return Product::where('supplier', $this->company_name)->count();
    }
}
