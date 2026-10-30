<?php

/**
 * ============================================================================
 * FILE PATH: database/migrations/2026_10_29_000002_create_stock_adjustments_table.php
 * DESCRIPTION: Creates the 'stock_adjustments' table for Module 5 of ISM System.
 *              Tracks stock additions, deductions, recounts, and approval status.
 * ============================================================================
 *
 * CONNECTED FILES:
 * - StockAdjustment Model (Backend): app/Models/StockAdjustment.php
 * - StockAdjustment Controller (Backend): app/Http/Controllers/Admin/StockAdjustmentController.php
 * - Product Model (Backend): app/Models/Product.php
 * - User Model (Backend): app/Models/User.php
 * - Stock Adjustments View (UI): resources/views/admin/stock_adjustments/index.blade.php
 * ============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PART 1: Run the migrations (Creates 'stock_adjustments' table).
     */
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique(); // e.g. ADJ-2026-001
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('adjustment_type'); // 'addition' (+ Stock) or 'subtraction' (- Stock)
            $table->integer('quantity'); // Always positive integer
            $table->integer('previous_stock')->default(0);
            $table->integer('new_stock')->default(0);
            $table->string('reason'); // e.g. Damaged, Audit Recount, Expired, Found Stock
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected'
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * PART 2: Reverse the migrations (Drops 'stock_adjustments' table).
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
