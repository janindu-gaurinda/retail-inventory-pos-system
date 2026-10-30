<?php

/**
 * ============================================================================
 * FILE PATH: app/Models/Category.php
 * DESCRIPTION: Eloquent Model for the 'categories' database table.
 *              Used to populate the Category dropdown in Add / Edit Product,
 *              Products List, and Suppliers Directory.
 * ============================================================================
 *
 * ROLE ACCESS PERMISSIONS:
 * - Admin (Owner / IT):               ✓ Full Access (Create, Edit, Delete)
 * - Store Manager:                    ✓ Full Access (Create, Edit, Delete)
 * - Stock Manager (Inventory Clerk):  ✓ Full Access (Create, Edit, Delete)
 * - Cashier:                          ❌ No Access
 *
 * CONNECTED FILES:
 * - Database Migration: database/migrations/2026_10_29_000007_create_categories_table.php
 * - Category Controller: app/Http/Controllers/Admin/CategoryController.php
 * - Product Controller: app/Http/Controllers/Admin/ProductController.php
 * - Add / Edit Product View: resources/views/admin/products/form.blade.php
 * - Categories Directory View: resources/views/admin/categories/index.blade.php
 * ============================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'category_code',
    'name',
    'description',
    'status',
])]
class Category extends Model
{
    /**
     * PART 1: Count of products currently assigned to this category.
     */
    public function productsCount(): int
    {
        return Product::where('category', $this->name)->count();
    }
}
