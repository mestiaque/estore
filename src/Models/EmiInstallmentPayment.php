<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class EmiInstallmentPayment extends Model
{
    protected $table = 'retail_emi_installment_payments';

    protected $fillable = [
        'emi_installment_id',
        'amount',
        'payment_method',
        'cash_register_session_id',
        'notes',
        'user_id',
        'paid_at',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function installment()
    {
        return $this->belongsTo(EmiInstallment::class, 'emi_installment_id');
    }

    public function cashRegisterSession()
    {
        return $this->belongsTo(CashRegisterSession::class);
    }
}
