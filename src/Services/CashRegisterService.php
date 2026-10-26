<?php

namespace ME\Retail\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use ME\Retail\Enums\CashRegisterSessionStatus;
use ME\Retail\Enums\CashRegisterTransactionType;
use ME\Retail\Models\CashRegister;
use ME\Retail\Models\CashRegisterSession;
use ME\Retail\Models\CashRegisterTransaction;

/**
 * Cash Register open/close workflow plus the single authorized way its cash
 * ledger is ever written to. Drawer balance is never a directly-editable
 * number — it is always opening_balance + SUM(transactions.amount).
 */
class CashRegisterService
{
    public function openSession(CashRegister $register, float $openingBalance, ?string $notes, int $userId): CashRegisterSession
    {
        return DB::transaction(function () use ($register, $openingBalance, $notes, $userId) {
            $existing = CashRegisterSession::query()
                ->where('cash_register_id', $register->id)
                ->where('status', CashRegisterSessionStatus::Open)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                throw new InvalidArgumentException("Register \"{$register->name}\" already has an open session.");
            }

            return CashRegisterSession::create([
                'cash_register_id' => $register->id,
                'opening_balance'  => $openingBalance,
                'status'           => CashRegisterSessionStatus::Open,
                'notes'            => $notes,
                'opened_by'        => $userId,
                'opened_at'        => now(),
            ]);
        });
    }

    public function closeSession(CashRegisterSession $session, float $countedClosingBalance, ?string $notes, int $userId): CashRegisterSession
    {
        return DB::transaction(function () use ($session, $countedClosingBalance, $notes, $userId) {
            $locked = CashRegisterSession::query()->whereKey($session->id)->lockForUpdate()->firstOrFail();

            if (!$locked->isOpen()) {
                throw new InvalidArgumentException('This register session is already closed.');
            }

            $expected   = (float) $locked->opening_balance + (float) $locked->transactions()->sum('amount');
            $difference = $countedClosingBalance - $expected;

            $locked->update([
                'closing_balance'          => $countedClosingBalance,
                'expected_closing_balance' => $expected,
                'difference'               => $difference,
                'status'                   => CashRegisterSessionStatus::Closed,
                'notes'                    => $notes ?: $locked->notes,
                'closed_by'                => $userId,
                'closed_at'                => now(),
            ]);

            return $locked;
        });
    }

    public function recordMovement(
        CashRegisterSession $session,
        CashRegisterTransactionType $type,
        float $amount,
        ?string $referenceType,
        int|string|null $referenceId,
        ?int $userId,
        ?string $notes
    ): CashRegisterTransaction {
        return DB::transaction(function () use ($session, $type, $amount, $referenceType, $referenceId, $userId, $notes) {
            $locked = CashRegisterSession::query()->whereKey($session->id)->lockForUpdate()->firstOrFail();

            if (!$locked->isOpen()) {
                throw new InvalidArgumentException('Cannot record a cash movement against a closed register session.');
            }

            $balanceAfter = (float) $locked->opening_balance + (float) $locked->transactions()->sum('amount') + $amount;

            return CashRegisterTransaction::create([
                'cash_register_session_id' => $session->id,
                'type'                     => $type,
                'amount'                   => $amount,
                'balance_after'            => $balanceAfter,
                'reference_type'           => $referenceType,
                'reference_id'             => $referenceId,
                'user_id'                  => $userId,
                'notes'                    => $notes,
            ]);
        });
    }
}
