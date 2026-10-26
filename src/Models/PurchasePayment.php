<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class PurchasePayment extends Model
{
    protected $table = 'retail_purchase_payments';

    protected $fillable = [
        'purchase_id',
        'supplier_id',
        'amount',
        'payment_method',
        'cash_register_session_id',
        'reference',
        'notes',
        'user_id',
        'paid_at',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function cashRegisterSession()
    {
        return $this->belongsTo(CashRegisterSession::class);
    }
}
