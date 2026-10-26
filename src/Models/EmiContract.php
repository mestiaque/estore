<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use ME\Retail\Enums\EmiContractStatus;

class EmiContract extends Model
{
    protected $table = 'retail_emi_contracts';

    protected $fillable = [
        'sale_id',
        'customer_id',
        'emi_bank_id',
        'emi_plan_id',
        'principal_amount',
        'interest_amount',
        'fee_amount',
        'total_payable',
        'down_payment',
        'financed_amount',
        'installment_count',
        'start_date',
        'status',
        'user_id',
    ];

    protected $casts = [
        'principal_amount'   => 'decimal:2',
        'interest_amount'    => 'decimal:2',
        'fee_amount'         => 'decimal:2',
        'total_payable'      => 'decimal:2',
        'down_payment'       => 'decimal:2',
        'financed_amount'    => 'decimal:2',
        'start_date'         => 'date',
        'status'             => EmiContractStatus::class,
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function bank()
    {
        return $this->belongsTo(EmiBank::class, 'emi_bank_id');
    }

    public function plan()
    {
        return $this->belongsTo(EmiPlan::class, 'emi_plan_id');
    }

    public function installments()
    {
        return $this->hasMany(EmiInstallment::class);
    }
}
