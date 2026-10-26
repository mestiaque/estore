<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $table = 'retail_stock_transfers';

    protected $fillable = [
        'from_warehouse_id',
        'to_warehouse_id',
        'transfer_number',
        'transfer_date',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }
}
