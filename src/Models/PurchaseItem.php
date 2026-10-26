<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $table = 'retail_purchase_items';

    protected $fillable = [
        'purchase_id',
        'product_id',
        'unit_id',
        'batch_id',
        'quantity',
        'quantity_base',
        'returned_quantity_base',
        'purchase_price',
        'discount',
        'tax',
        'total',
    ];

    protected $casts = [
        'quantity'                => 'decimal:4',
        'quantity_base'           => 'decimal:4',
        'returned_quantity_base'  => 'decimal:4',
        'purchase_price'          => 'decimal:2',
        'discount'                => 'decimal:2',
        'tax'                     => 'decimal:2',
        'total'                   => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function serials()
    {
        return $this->hasMany(Serial::class);
    }

    public function remainingReturnableBaseQuantity(): float
    {
        return (float) $this->quantity_base - (float) $this->returned_quantity_base;
    }
}
