<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $table = 'retail_batches';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'batch_number',
        'barcode',
        'expiry_date',
        'purchase_price',
        'quantity',
        'status',
    ];

    protected $casts = [
        'expiry_date'    => 'date',
        'purchase_price' => 'decimal:2',
        'quantity'       => 'decimal:4',
        'status'         => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }
}
