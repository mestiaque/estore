<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $table = 'retail_customers';

    protected $fillable = [
        'name',
        'customer_code',
        'phone',
        'email',
        'address',
        'opening_balance',
        'credit_limit',
        'customer_level_id',
        'membership_id',
        'membership_expires_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'opening_balance'        => 'decimal:2',
        'credit_limit'           => 'decimal:2',
        'membership_expires_at'  => 'date',
        'status'                 => 'boolean',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function transactions()
    {
        return $this->hasMany(CustomerTransaction::class);
    }

    public function loyaltyTransactions()
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function level()
    {
        return $this->belongsTo(CustomerLevel::class, 'customer_level_id');
    }

    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    /**
     * Current amount owed by this customer: opening balance plus every
     * ledger movement since (credit sales increase it, payments/returns decrease it).
     */
    public function getDueBalanceAttribute(): float
    {
        return (float) $this->opening_balance + (float) $this->transactions()->sum('amount');
    }

    /**
     * Current redeemable point balance: SUM of every loyalty ledger movement.
     */
    public function getPointsBalanceAttribute(): float
    {
        return (float) $this->loyaltyTransactions()->sum('points');
    }

    public function hasActiveMembership(): bool
    {
        if (!$this->membership_id) {
            return false;
        }

        return $this->membership_expires_at === null || !$this->membership_expires_at->isPast();
    }
}
