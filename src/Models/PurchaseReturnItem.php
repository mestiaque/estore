<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    protected $table = 'retail_purchase_return_items';

    protected $fillable = [
        'purchase_return_id',
        'purchase_item_id',
        'product_id',
        'serial_id',
        'quantity',
        'quantity_base',
        'refund_amount',
    ];

    protected $casts = [
        'quantity'      => 'decimal:4',
        'quantity_base' => 'decimal:4',
        'refund_amount' => 'decimal:2',
    ];

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function serial()
    {
        return $this->belongsTo(Serial::class);
    }
}
