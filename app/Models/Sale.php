<?php

/**
 * ============================================================================
 * FILE PATH: app/Models/Sale.php
 * DESCRIPTION: Eloquent Model for the 'sales' table (POS Invoices).
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Database Migration: database/migrations/2026_10_29_000005_create_sales_table.php
 * - SaleItem Model: app/Models/SaleItem.php
 * - POS Controller: app/Http/Controllers/Admin/PosController.php
 * - User Model (Cashier): app/Models/User.php
 * - POS Checkout UI: resources/views/admin/pos/index.blade.php
 * ============================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'invoice_no',
    'user_id',
    'customer_name',
    'customer_phone',
    'items_count',
    'subtotal',
    'discount_amount',
    'tax_amount',
    'grand_total',
    'paid_amount',
    'change_amount',
    'payment_method',
    'status',
])]
class Sale extends Model
{
    protected function casts(): array
    {
        return [
            'items_count' => 'integer',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
        ];
    }

    /**
     * PART 1: Relationship to the Cashier / User who processed the sale.
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * PART 2: Relationship to line items on this invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
