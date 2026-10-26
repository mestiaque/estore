<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use ME\Retail\Enums\CashRegisterSessionStatus;

class CashRegisterSession extends Model
{
    protected $table = 'retail_cash_register_sessions';

    protected $fillable = [
        'cash_register_id',
        'opening_balance',
        'closing_balance',
        'expected_closing_balance',
        'difference',
        'status',
        'notes',
        'opened_by',
        'closed_by',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'opening_balance'          => 'decimal:2',
        'closing_balance'          => 'decimal:2',
        'expected_closing_balance' => 'decimal:2',
        'difference'               => 'decimal:2',
        'status'                   => CashRegisterSessionStatus::class,
        'opened_at'                => 'datetime',
        'closed_at'                => 'datetime',
    ];

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function transactions()
    {
        return $this->hasMany(CashRegisterTransaction::class);
    }

    public function openedBy()
    {
        return $this->belongsTo(\ME\Models\User::class, 'opened_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(\ME\Models\User::class, 'closed_by');
    }

    public function isOpen(): bool
    {
        return $this->status === CashRegisterSessionStatus::Open;
    }

    /**
     * Cash that should be in the drawer right now: opening balance plus every
     * ledger movement since. Never a directly-editable number.
     */
    public function getCurrentBalanceAttribute(): float
    {
        return (float) $this->opening_balance + (float) $this->transactions()->sum('amount');
    }
}
