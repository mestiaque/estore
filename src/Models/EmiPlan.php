<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use ME\Retail\Enums\EmiFeeType;
use ME\Retail\Enums\EmiInterestType;

class EmiPlan extends Model
{
    protected $table = 'retail_emi_plans';

    protected $fillable = [
        'emi_bank_id',
        'name',
        'duration_months',
        'interest_type',
        'interest_rate',
        'fee_type',
        'fee_value',
        'min_amount',
        'max_amount',
        'status',
    ];

    protected $casts = [
        'interest_type'   => EmiInterestType::class,
        'interest_rate'   => 'decimal:2',
        'fee_type'        => EmiFeeType::class,
        'fee_value'       => 'decimal:2',
        'min_amount'      => 'decimal:2',
        'max_amount'      => 'decimal:2',
        'status'          => 'boolean',
    ];

    public function bank()
    {
        return $this->belongsTo(EmiBank::class, 'emi_bank_id');
    }
}
