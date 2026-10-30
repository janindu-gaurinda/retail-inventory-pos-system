<?php

/**
 * ============================================================================
 * FILE PATH: app/Models/StockAdjustment.php
 * DESCRIPTION: Eloquent Model for the 'stock_adjustments' table in ISM System.
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Database Migration: database/migrations/2026_10_29_000002_create_stock_adjustments_table.php
 * - StockAdjustment Controller: app/Http/Controllers/Admin/StockAdjustmentController.php
 * - Product Model: app/Models/Product.php
 * - User Model: app/Models/User.php
 * - Stock Adjustments UI: resources/views/admin/stock_adjustments/index.blade.php
 * ============================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'reference_no',
    'product_id',
    'adjustment_type',
    'quantity',
    'previous_stock',
    'new_stock',
    'reason',
    'notes',
    'status',
    'requested_by',
    'approved_by',
    'approved_at',
])]
class StockAdjustment extends Model
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
            'previous_stock' => 'integer',
            'new_stock' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * PART 2: Relationship to the Product being adjusted (app/Models/Product.php).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * PART 3: Relationship to the User who created/requested the adjustment.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * PART 4: Relationship to the Admin / Store Manager who approved/rejected it.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * PART 5: UI Badge Helper for Adjustment Status ('pending', 'approved', 'rejected').
     */
    public function statusBadge(): array
    {
        return match ($this->status) {
            'approved' => ['label' => 'Approved', 'class' => 'text-bg-success'],
            'rejected' => ['label' => 'Rejected', 'class' => 'text-bg-danger'],
            default => ['label' => 'Pending Approval', 'class' => 'text-bg-warning'],
        };
    }
}
