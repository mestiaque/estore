<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $table = 'retail_sale_items';

    protected $fillable = [
        'sale_id',
        'product_id',
        'unit_id',
        'batch_id',
        'quantity',
        'quantity_base',
        'returned_quantity_base',
        'sale_price',
        'cost_price',
        'discount',
        'auto_discount',
        'discount_rule_id',
        'tax',
        'total',
        'points_earned',
    ];

    protected $casts = [
        'quantity'                => 'decimal:4',
        'quantity_base'           => 'decimal:4',
        'returned_quantity_base'  => 'decimal:4',
        'sale_price'              => 'decimal:2',
        'cost_price'              => 'decimal:2',
        'discount'                => 'decimal:2',
        'auto_discount'           => 'decimal:2',
        'tax'                     => 'decimal:2',
        'total'                   => 'decimal:2',
        'points_earned'           => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
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

    public function discountRule()
    {
        return $this->belongsTo(DiscountRule::class);
    }

    public function remainingReturnableBaseQuantity(): float
    {
        return (float) $this->quantity_base - (float) $this->returned_quantity_base;
    }
}
