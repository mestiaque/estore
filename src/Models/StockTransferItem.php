<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransferItem extends Model
{
    protected $table = 'retail_stock_transfer_items';

    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'unit_id',
        'source_batch_id',
        'destination_batch_id',
        'quantity',
        'quantity_base',
    ];

    protected $casts = [
        'quantity'      => 'decimal:4',
        'quantity_base' => 'decimal:4',
    ];

    public function stockTransfer()
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function sourceBatch()
    {
        return $this->belongsTo(Batch::class, 'source_batch_id');
    }

    public function destinationBatch()
    {
        return $this->belongsTo(Batch::class, 'destination_batch_id');
    }
}
