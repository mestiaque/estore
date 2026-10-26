<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    protected $table = 'retail_sale_returns';

    protected $fillable = [
        'sale_id',
        'customer_id',
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

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(SaleReturnItem::class);
    }
}
