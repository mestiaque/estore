<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    protected $table = 'retail_sale_payments';

    protected $fillable = [
        'sale_id',
        'customer_id',
        'amount',
        'payment_method',
        'cash_register_session_id',
        'reference',
        'user_id',
        'paid_at',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashRegisterSession()
    {
        return $this->belongsTo(CashRegisterSession::class);
    }
}
