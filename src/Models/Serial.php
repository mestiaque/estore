<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use ME\Retail\Enums\SerialStatus;

class Serial extends Model
{
    protected $table = 'retail_serials';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'purchase_item_id',
        'sale_item_id',
        'serial_number',
        'status',
        'purchase_price',
        'warranty_months',
        'warranty_start',
        'warranty_end',
    ];

    protected $casts = [
        'status'          => SerialStatus::class,
        'purchase_price'  => 'decimal:2',
        'warranty_start'  => 'date',
        'warranty_end'    => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }
}
