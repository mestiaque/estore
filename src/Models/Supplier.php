<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $table = 'retail_suppliers';

    protected $fillable = [
        'name',
        'company',
        'phone',
        'email',
        'address',
        'tax_number',
        'opening_balance',
        'credit_limit',
        'status',
        'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'credit_limit'    => 'decimal:2',
        'status'          => 'boolean',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function transactions()
    {
        return $this->hasMany(SupplierTransaction::class);
    }

    /**
     * Current amount owed to this supplier: opening balance plus every
     * ledger movement since (purchases increase it, payments decrease it).
     */
    public function getDueBalanceAttribute(): float
    {
        return (float) $this->opening_balance + (float) $this->transactions()->sum('amount');
    }
}
