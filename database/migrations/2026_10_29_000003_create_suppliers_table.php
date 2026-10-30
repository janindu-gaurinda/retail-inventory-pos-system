<?php

/**
 * ============================================================================
 * FILE PATH: database/migrations/2026_10_29_000003_create_suppliers_table.php
 * DESCRIPTION: Creates the 'suppliers' table for Module 6 (Suppliers Directory)
 *              in the ISM System.
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Supplier Model (Backend): app/Models/Supplier.php
 * - Supplier Controller (Backend CRUD): app/Http/Controllers/Admin/SupplierController.php
 * - Database Seeder (Backend): database/seeders/DatabaseSeeder.php
 * - Suppliers Directory View (UI): resources/views/admin/suppliers/index.blade.php
 * ============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PART 1: Run the migrations (Creates 'suppliers' table).
     */
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_code')->unique(); // e.g. SUP-001
            $table->string('company_name');
            $table->string('contact_person');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('category')->default('General');
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('payment_terms')->default('Net 30'); // e.g. Cash on Delivery, Net 15, Net 30
            $table->decimal('outstanding_balance', 12, 2)->default(0);
            $table->string('status')->default('active'); // 'active' or 'inactive'
            $table->timestamps();
        });
    }

    /**
     * PART 2: Reverse the migrations (Drops 'suppliers' table).
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
