<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use ME\Retail\Enums\StockAdjustmentReason;

class StockAdjustment extends Model
{
    protected $table = 'retail_stock_adjustments';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'stock_ledger_id',
        'quantity_before',
        'quantity_adjusted',
        'quantity_after',
        'reason',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'reason'             => StockAdjustmentReason::class,
        'quantity_before'    => 'decimal:4',
        'quantity_adjusted'  => 'decimal:4',
        'quantity_after'     => 'decimal:4',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function ledger()
    {
        return $this->belongsTo(StockLedger::class, 'stock_ledger_id');
    }
}
