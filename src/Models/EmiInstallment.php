<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use ME\Retail\Enums\EmiInstallmentStatus;

class EmiInstallment extends Model
{
    protected $table = 'retail_emi_installments';

    protected $fillable = [
        'emi_contract_id',
        'installment_number',
        'due_date',
        'amount',
        'paid_amount',
        'remaining_amount',
        'late_fee',
        'paid_date',
        'status',
    ];

    protected $casts = [
        'due_date'          => 'date',
        'amount'            => 'decimal:2',
        'paid_amount'       => 'decimal:2',
        'remaining_amount'  => 'decimal:2',
        'late_fee'          => 'decimal:2',
        'paid_date'         => 'date',
        'status'            => EmiInstallmentStatus::class,
    ];

    public function contract()
    {
        return $this->belongsTo(EmiContract::class, 'emi_contract_id');
    }

    public function payments()
    {
        return $this->hasMany(EmiInstallmentPayment::class);
    }

    public function isOverdue(): bool
    {
        return $this->status !== EmiInstallmentStatus::Paid
            && $this->status !== EmiInstallmentStatus::Cancelled
            && $this->due_date->isPast();
    }
}
