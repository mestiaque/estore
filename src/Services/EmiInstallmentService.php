<?php

namespace ME\Retail\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use ME\Retail\Enums\CashRegisterTransactionType;
use ME\Retail\Enums\CustomerTransactionType;
use ME\Retail\Enums\EmiContractStatus;
use ME\Retail\Enums\EmiInstallmentStatus;
use ME\Retail\Enums\PaymentStatus;
use ME\Retail\Models\CashRegisterSession;
use ME\Retail\Models\EmiContract;
use ME\Retail\Models\EmiInstallment;
use ME\Retail\Models\EmiInstallmentPayment;

/**
 * EMI installment payments — keeps the sale's cached paid/due amount and the
 * customer ledger consistent with every installment paid (plan §86: never
 * allow an inconsistent EMI balance).
 */
class EmiInstallmentService
{
    public function __construct(
        protected CustomerLedgerService $customerLedgerService,
        protected CashRegisterService $cashRegisterService
    ) {
    }

    public function pay(
        EmiInstallment $installment,
        float $amount,
        string $method,
        ?int $userId,
        ?int $cashRegisterSessionId = null,
        ?string $notes = null
    ): EmiInstallmentPayment {
        return DB::transaction(function () use ($installment, $amount, $method, $userId, $cashRegisterSessionId, $notes) {
            if ($amount <= 0) {
                throw new InvalidArgumentException('Payment amount must be greater than zero.');
            }

            if ($amount > (float) $installment->remaining_amount + 0.0001) {
                throw new InvalidArgumentException('Payment amount cannot exceed the installment\'s remaining balance.');
            }

            $session = ($method === 'cash' && $cashRegisterSessionId) ? CashRegisterSession::findOrFail($cashRegisterSessionId) : null;

            $payment = EmiInstallmentPayment::create([
                'emi_installment_id'        => $installment->id,
                'amount'                     => $amount,
                'payment_method'             => $method,
                'cash_register_session_id'   => $session?->id,
                'notes'                      => $notes,
                'user_id'                    => $userId,
                'paid_at'                    => now(),
            ]);

            $paidAmount     = (float) $installment->paid_amount + $amount;
            $remainingAmount = max((float) $installment->amount - $paidAmount, 0);

            $installment->update([
                'paid_amount'       => $paidAmount,
                'remaining_amount'  => $remainingAmount,
                'status'            => $remainingAmount <= 0 ? EmiInstallmentStatus::Paid : EmiInstallmentStatus::Partial,
                'paid_date'         => $remainingAmount <= 0 ? now()->toDateString() : $installment->paid_date,
            ]);

            $contract = $installment->contract;
            $sale     = $contract->sale;

            $this->customerLedgerService->record(
                customer: $contract->customer,
                type: CustomerTransactionType::Payment,
                amount: -$amount,
                referenceType: EmiInstallment::class,
                referenceId: $installment->id,
                userId: $userId,
                notes: "EMI installment #{$installment->installment_number} for sale {$sale->invoice_number}"
            );

            $salePaidAmount = (float) $sale->paid_amount + $amount;
            $sale->update([
                'paid_amount'    => $salePaidAmount,
                'due_amount'     => max((float) $sale->grand_total - $salePaidAmount, 0),
                'payment_status' => PaymentStatus::fromAmounts((float) $sale->grand_total, $salePaidAmount),
            ]);

            if ($session) {
                $this->cashRegisterService->recordMovement(
                    session: $session,
                    type: CashRegisterTransactionType::Sale,
                    amount: $amount,
                    referenceType: EmiInstallment::class,
                    referenceId: $installment->id,
                    userId: $userId,
                    notes: "EMI installment #{$installment->installment_number} for sale {$sale->invoice_number}"
                );
            }

            $remainingInstallments = $contract->installments()
                ->where('status', '!=', EmiInstallmentStatus::Paid)
                ->where('status', '!=', EmiInstallmentStatus::Cancelled)
                ->count();

            if ($remainingInstallments === 0) {
                $contract->update(['status' => EmiContractStatus::Completed]);
            }

            return $payment;
        });
    }

    /**
     * Marks any pending/partial installment whose due date has passed as overdue.
     * Call from a scheduled command; purely a status refresh, no financial effect.
     */
    public function markOverdue(): int
    {
        return EmiInstallment::query()
            ->whereIn('status', [EmiInstallmentStatus::Pending, EmiInstallmentStatus::Partial])
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => EmiInstallmentStatus::Overdue]);
    }
}
