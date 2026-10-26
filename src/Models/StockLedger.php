<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use ME\Retail\Enums\StockMovementType;

/**
 * Append-only stock movement history. Never update() or delete() a row here —
 * corrections are made by inserting a new offsetting movement.
 */
class StockLedger extends Model
{
    const UPDATED_AT = null;

    protected $table = 'retail_stock_ledgers';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'movement_type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reference_type',
        'reference_id',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'movement_type'   => StockMovementType::class,
        'quantity'        => 'decimal:4',
        'quantity_before' => 'decimal:4',
        'quantity_after'  => 'decimal:4',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
