<?php

/**
 * ============================================================================
 * FILE PATH: app/Models/PurchaseOrder.php
 * DESCRIPTION: Eloquent Model for the 'purchase_orders' table (Module 7 — Purchase Orders).
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Database Migration: database/migrations/2026_10_29_000004_create_purchase_orders_table.php
 * - PurchaseOrder Controller: app/Http/Controllers/Admin/PurchaseOrderController.php
 * - Supplier Model: app/Models/Supplier.php
 * - Product Model: app/Models/Product.php
 * - User Model: app/Models/User.php
 * - Purchase Orders UI: resources/views/admin/purchase_orders/index.blade.php
 * ============================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'po_number',
    'supplier_id',
    'product_id',
    'quantity',
    'unit_cost',
    'total_amount',
    'expected_date',
    'status',
    'notes',
    'created_by',
    'received_by',
    'received_at',
])]
class PurchaseOrder extends Model
{
    /**
     * PART 1: Attribute type casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_cost' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'expected_date' => 'date',
            'received_at' => 'datetime',
        ];
    }

    /**
     * PART 2: Relationship to the Supplier (app/Models/Supplier.php).
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * PART 3: Relationship to the ordered Product (app/Models/Product.php).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * PART 4: Relationship to the User who created the PO.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * PART 5: Relationship to the User who received the PO stock.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * PART 6: UI Badge Helper for PO Status ('ordered', 'received', 'cancelled').
     */
    public function statusBadge(): array
    {
        return match ($this->status) {
            'received' => ['label' => 'Received into Stock', 'class' => 'text-bg-success'],
            'cancelled' => ['label' => 'Cancelled', 'class' => 'text-bg-danger'],
            default => ['label' => 'Ordered (Awaiting Delivery)', 'class' => 'text-bg-warning'],
        };
    }
}
