<?php

/**
 * ============================================================================
 * FILE PATH: app/Models/SaleItem.php
 * DESCRIPTION: Eloquent Model for the 'sale_items' table (POS Line Items).
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Database Migration: database/migrations/2026_10_29_000005_create_sales_table.php
 * - Sale Model: app/Models/Sale.php
 * - Product Model: app/Models/Product.php
 * - POS Controller: app/Http/Controllers/Admin/PosController.php
 * ============================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sale_id',
    'product_id',
    'product_name',
    'sku',
    'quantity',
    'unit_price',
    'line_total',
])]
class SaleItem extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
