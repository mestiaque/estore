<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use ME\Models\User;

class HeldSale extends Model
{
    protected $table = 'retail_held_sales';

    protected $fillable = [
        'customer_id',
        'warehouse_id',
        'reference',
        'payload',
        'item_count',
        'estimated_total',
        'user_id',
    ];

    protected $casts = [
        'payload'         => 'array',
        'estimated_total' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
