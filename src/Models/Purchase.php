<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use ME\Retail\Enums\PaymentStatus;

class Purchase extends Model
{
    protected $table = 'retail_purchases';

    protected $fillable = [
        'supplier_id',
        'store_id',
        'warehouse_id',
        'invoice_number',
        'reference_number',
        'purchase_date',
        'subtotal',
        'discount',
        'tax',
        'grand_total',
        'paid_amount',
        'due_amount',
        'payment_status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'purchase_date'  => 'date',
        'subtotal'       => 'decimal:2',
        'discount'       => 'decimal:2',
        'tax'            => 'decimal:2',
        'grand_total'    => 'decimal:2',
        'paid_amount'    => 'decimal:2',
        'due_amount'     => 'decimal:2',
        'payment_status' => PaymentStatus::class,
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
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
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }
}
