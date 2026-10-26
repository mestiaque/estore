<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use ME\Retail\Enums\LoyaltyTransactionType;

/**
 * Append-only points ledger row. Never update() or delete() — corrections
 * are made by inserting a new offsetting transaction.
 */
class LoyaltyTransaction extends Model
{
    const UPDATED_AT = null;

    protected $table = 'retail_loyalty_transactions';

    protected $fillable = [
        'customer_id',
        'type',
        'points',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'type'          => LoyaltyTransactionType::class,
        'points'        => 'decimal:2',
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
