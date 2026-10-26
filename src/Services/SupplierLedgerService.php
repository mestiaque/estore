<?php

namespace ME\Retail\Services;

use Illuminate\Support\Facades\DB;
use ME\Retail\Enums\SupplierTransactionType;
use ME\Retail\Models\Supplier;
use ME\Retail\Models\SupplierTransaction;

/**
 * The single authorized way the supplier ledger is ever written to.
 * Supplier due is never stored as a directly-editable number — it is always
 * opening_balance + SUM(transactions.amount).
 */
class SupplierLedgerService
{
    /**
     * @param float $amount Signed. Positive increases what we owe the supplier (e.g. a purchase);
     *                       negative decreases it (a payment or a purchase return credit).
     */
    public function record(
        Supplier $supplier,
        SupplierTransactionType $type,
        float $amount,
        ?string $referenceType = null,
        int|string|null $referenceId = null,
        ?int $userId = null,
        ?string $notes = null
    ): SupplierTransaction {
        return DB::transaction(function () use ($supplier, $type, $amount, $referenceType, $referenceId, $userId, $notes) {
            $locked = Supplier::query()->whereKey($supplier->id)->lockForUpdate()->firstOrFail();

            $balanceAfter = (float) $locked->opening_balance
                + (float) $locked->transactions()->sum('amount')
                + $amount;

            return SupplierTransaction::create([
                'supplier_id'    => $supplier->id,
                'type'           => $type,
                'amount'         => $amount,
                'balance_after'  => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'user_id'        => $userId,
                'notes'          => $notes,
            ]);
        });
    }
}
