<?php

/**
 * ============================================================================
 * FILE PATH: database/migrations/2026_10_29_000005_create_sales_table.php
 * DESCRIPTION: Creates the 'sales' and 'sale_items' tables for Module 8
 *              (POS Checkout Screen) and Module 9 (Sales History / Invoices).
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Sale Model (Backend): app/Models/Sale.php
 * - SaleItem Model (Backend): app/Models/SaleItem.php
 * - POS Controller (Backend): app/Http/Controllers/Admin/PosController.php
 * - Product Model (Stock Deduction): app/Models/Product.php
 * - POS Checkout View (UI): resources/views/admin/pos/index.blade.php
 * ============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PART 1: Run the migrations (Creates 'sales' and 'sale_items' tables).
     */
    public function up(): void
    {
        // Table 1: 'sales' (POS Invoice Header)
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique(); // e.g. INV-2026-0001
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Cashier / Admin
            $table->string('customer_name')->default('Walk-in Customer');
            $table->string('customer_phone')->nullable();
            $table->integer('items_count')->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('change_amount', 12, 2)->default(0);
            $table->string('payment_method')->default('Cash'); // Cash, Card, QR / Bank
            $table->string('status')->default('completed'); // completed, refunded
            $table->timestamps();
        });

        // Table 2: 'sale_items' (Individual Line Items on POS Invoice)
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');
            $table->string('sku');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * PART 2: Reverse the migrations (Drops 'sale_items' and 'sales' tables).
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
