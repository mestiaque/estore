<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use ME\Retail\Enums\CashRegisterTransactionType;

/**
 * Append-only cash movement row. Never update() or delete() — corrections
 * are made by inserting a new offsetting movement.
 */
class CashRegisterTransaction extends Model
{
    const UPDATED_AT = null;

    protected $table = 'retail_cash_register_transactions';

    protected $fillable = [
        'cash_register_session_id',
        'type',
        'amount',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'type'          => CashRegisterTransactionType::class,
        'amount'        => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function session()
    {
        return $this->belongsTo(CashRegisterSession::class, 'cash_register_session_id');
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
