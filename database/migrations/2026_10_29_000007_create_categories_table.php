<?php

/**
 * ============================================================================
 * FILE PATH: database/migrations/2026_10_29_000007_create_categories_table.php
 * DESCRIPTION: Database migration for the 'categories' table.
 *              Stores product categories used in the Add/Edit Product dropdown,
 *              Products List filter, and Suppliers Directory.
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Model: app/Models/Category.php
 * - Controller: app/Http/Controllers/Admin/CategoryController.php
 * - Product Controller: app/Http/Controllers/Admin/ProductController.php
 * - Add/Edit Product UI: resources/views/admin/products/form.blade.php
 * - Categories Management UI: resources/views/admin/categories/index.blade.php
 * ============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PART 1: Create the 'categories' table.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_code', 40)->unique();
            $table->string('name', 120)->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * PART 2: Drop the 'categories' table on rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
