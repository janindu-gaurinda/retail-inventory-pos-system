<?php

/**
 * ============================================================================
 * FILE PATH: database/migrations/2026_10_29_000004_create_purchase_orders_table.php
 * DESCRIPTION: Creates the 'purchase_orders' table for Module 7 — Purchase Orders (POs).
 * ============================================================================
 *
 * CONNECTED FILES:
 * - PurchaseOrder Model (Backend): app/Models/PurchaseOrder.php
 * - PurchaseOrder Controller (Backend): app/Http/Controllers/Admin/PurchaseOrderController.php
 * - Supplier Model (Backend): app/Models/Supplier.php
 * - Product Model (Backend): app/Models/Product.php
 * - Purchase Orders View (UI): resources/views/admin/purchase_orders/index.blade.php
 * ============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PART 1: Run the migrations (Creates 'purchase_orders' table).
     */
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique(); // e.g. PO-2026-001
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->date('expected_date')->nullable();
            $table->string('status')->default('ordered'); // 'ordered', 'received', 'cancelled'
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * PART 2: Reverse the migrations (Drops 'purchase_orders' table).
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
