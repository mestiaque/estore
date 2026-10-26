<?php

namespace ME\Retail\Services;

use Illuminate\Support\Facades\DB;
use ME\Retail\Enums\CustomerTransactionType;
use ME\Retail\Models\Customer;
use ME\Retail\Models\CustomerTransaction;

/**
 * The single authorized way the customer ledger is ever written to.
 * Customer due is never stored as a directly-editable number — it is always
 * opening_balance + SUM(transactions.amount).
 */
class CustomerLedgerService
{
    /**
     * @param float $amount Signed. Positive increases what the customer owes us (a credit sale);
     *                       negative decreases it (a payment received or a sale return credit).
     */
    public function record(
        Customer $customer,
        CustomerTransactionType $type,
        float $amount,
        ?string $referenceType = null,
        int|string|null $referenceId = null,
        ?int $userId = null,
        ?string $notes = null
    ): CustomerTransaction {
        return DB::transaction(function () use ($customer, $type, $amount, $referenceType, $referenceId, $userId, $notes) {
            $locked = Customer::query()->whereKey($customer->id)->lockForUpdate()->firstOrFail();

            $balanceAfter = (float) $locked->opening_balance
                + (float) $locked->transactions()->sum('amount')
                + $amount;

            return CustomerTransaction::create([
                'customer_id'    => $customer->id,
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
