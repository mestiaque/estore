<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use ME\Retail\Enums\SupplierTransactionType;

/**
 * Append-only supplier ledger row. Never update() or delete() — corrections
 * are made by inserting a new offsetting transaction.
 */
class SupplierTransaction extends Model
{
    const UPDATED_AT = null;

    protected $table = 'retail_supplier_transactions';

    protected $fillable = [
        'supplier_id',
        'type',
        'amount',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'type'          => SupplierTransactionType::class,
        'amount'        => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
