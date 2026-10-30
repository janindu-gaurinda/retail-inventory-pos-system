<?php

/**
 * ============================================================================
 * FILE PATH: database/migrations/2026_10_29_000001_create_products_table.php
 * DESCRIPTION: Creates the 'products' table for the ISM Inventory & POS System.
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Product Model (Backend): app/Models/Product.php
 * - Product Controller (Backend CRUD): app/Http/Controllers/Admin/ProductController.php
 * - Database Seeder (Backend): database/seeders/DatabaseSeeder.php
 * - Products List View (UI): resources/views/admin/products/index.blade.php
 * ============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PART 1: Run the migrations (Creates 'products' table).
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('barcode')->unique();
            $table->string('name');
            $table->string('category');
            $table->string('supplier')->nullable();
            $table->string('unit')->default('pcs');
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock_level')->default(10);
            $table->string('status')->default('active'); // 'active' or 'inactive'
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * PART 2: Reverse the migrations (Drops 'products' table).
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
