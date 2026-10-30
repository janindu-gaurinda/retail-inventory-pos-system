<?php

/**
 * ============================================================================
 * FILE PATH: app/Models/Product.php
 * DESCRIPTION: Eloquent Model for the 'products' table in ISM System.
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Database Migration: database/migrations/2026_10_29_000001_create_products_table.php
 * - Database Seeder: database/seeders/DatabaseSeeder.php
 * - Product Controller (CRUD Backend): app/Http/Controllers/Admin/ProductController.php
 * - Dashboard Controller (Stats Backend): app/Http/Controllers/Admin/DashboardController.php
 * - Products List UI: resources/views/admin/products/index.blade.php
 * ============================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'sku',
    'barcode',
    'name',
    'category',
    'supplier',
    'unit',
    'cost_price',
    'selling_price',
    'stock_quantity',
    'min_stock_level',
    'status',
    'description',
])]
class Product extends Model
{
    /**
     * PART 1: Attribute type casting for decimal & integer columns.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'min_stock_level' => 'integer',
        ];
    }

    /**
     * PART 2: Helper to determine stock health status ('Out of Stock', 'Low Stock', 'In Stock').
     * Used in UI badges on resources/views/admin/products/index.blade.php
     */
    public function stockBadge(): array
    {
        if ($this->stock_quantity <= 0) {
            return ['label' => 'Out of Stock', 'class' => 'text-bg-danger'];
        }

        if ($this->stock_quantity <= $this->min_stock_level) {
            return ['label' => 'Low Stock', 'class' => 'text-bg-warning'];
        }

        return ['label' => 'In Stock', 'class' => 'text-bg-success'];
    }

    /**
     * PART 3: Profit per unit & profit margin percentage calculation.
     * Shown only to roles permitted to view Cost Price (Admin, Store Manager, Stock Manager).
     */
    public function profitMarginPercent(): float
    {
        if ((float) $this->selling_price <= 0) {
            return 0.0;
        }

        $profit = (float) $this->selling_price - (float) $this->cost_price;

        return round(($profit / (float) $this->selling_price) * 100, 1);
    }
}
