<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Performance cache of current per-warehouse stock, derived from
 * retail_stock_ledgers. Only ever written through StockService.
 */
class ProductStock extends Model
{
    protected $table = 'retail_product_stocks';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
