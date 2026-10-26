<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    protected $table = 'retail_purchase_returns';

    protected $fillable = [
        'purchase_id',
        'supplier_id',
        'warehouse_id',
        'return_date',
        'total_refund',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'return_date'  => 'date',
        'total_refund' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }
}
