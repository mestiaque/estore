<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use ME\Retail\Enums\CustomerTransactionType;

/**
 * Append-only customer ledger row. Never update() or delete() — corrections
 * are made by inserting a new offsetting transaction.
 */
class CustomerTransaction extends Model
{
    const UPDATED_AT = null;

    protected $table = 'retail_customer_transactions';

    protected $fillable = [
        'customer_id',
        'type',
        'amount',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'type'          => CustomerTransactionType::class,
        'amount'        => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
