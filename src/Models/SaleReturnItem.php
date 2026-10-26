<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturnItem extends Model
{
    protected $table = 'retail_sale_return_items';

    protected $fillable = [
        'sale_return_id',
        'sale_item_id',
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

    public function saleReturn()
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
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
