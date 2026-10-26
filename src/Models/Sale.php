<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use ME\Retail\Enums\PaymentStatus;

class Sale extends Model
{
    protected $table = 'retail_sales';

    protected $fillable = [
        'customer_id',
        'store_id',
        'warehouse_id',
        'invoice_number',
        'sale_date',
        'subtotal',
        'discount',
        'auto_discount',
        'coupon_code',
        'discount_rule_id',
        'tax',
        'grand_total',
        'paid_amount',
        'due_amount',
        'payment_status',
        'points_earned',
        'points_redeemed',
        'points_discount',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'sale_date'       => 'date',
        'subtotal'        => 'decimal:2',
        'discount'        => 'decimal:2',
        'auto_discount'   => 'decimal:2',
        'tax'             => 'decimal:2',
        'grand_total'     => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'due_amount'      => 'decimal:2',
        'payment_status'  => PaymentStatus::class,
        'points_earned'   => 'decimal:2',
        'points_redeemed' => 'decimal:2',
        'points_discount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }

    public function returns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function cashier()
    {
        return $this->belongsTo(\ME\Models\User::class, 'user_id');
    }

    public function couponRule()
    {
        return $this->belongsTo(DiscountRule::class, 'discount_rule_id');
    }

    public function emiContract()
    {
        return $this->hasOne(EmiContract::class);
    }
}
