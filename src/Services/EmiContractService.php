<?php

namespace ME\Retail\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use ME\Retail\Enums\CustomerTransactionType;
use ME\Retail\Enums\EmiContractStatus;
use ME\Retail\Enums\EmiInstallmentStatus;
use ME\Retail\Enums\PaymentStatus;
use ME\Retail\Models\EmiBank;
use ME\Retail\Models\EmiContract;
use ME\Retail\Models\EmiInstallment;
use ME\Retail\Models\EmiPlan;
use ME\Retail\Models\Sale;

/**
 * Sale -> EMI Plan -> Bank -> EMI Contract -> Installments (plan §38). An EMI
 * contract finances a sale's existing due amount — it does not duplicate
 * customer-debt tracking, it schedules the debt the sale already recorded.
 */
class EmiContractService
{
    public function __construct(
        protected EmiCalculationService $calculationService,
        protected CustomerLedgerService $customerLedgerService
    ) {
    }

    public function create(Sale $sale, EmiBank $bank, EmiPlan $plan, float $downPayment, string $startDate, ?int $userId): EmiContract
    {
        return DB::transaction(function () use ($sale, $bank, $plan, $downPayment, $startDate, $userId) {
            if (!$sale->customer_id) {
                throw new InvalidArgumentException('EMI can only be set up for a sale with a registered customer.');
            }

            $principal = (float) $sale->due_amount;

            if ($principal <= 0) {
                throw new InvalidArgumentException('This sale has no remaining due amount to finance.');
            }

            if ($principal < (float) $plan->min_amount || ($plan->max_amount !== null && $principal > (float) $plan->max_amount)) {
                throw new InvalidArgumentException("This plan only covers amounts between {$plan->min_amount} and " . ($plan->max_amount ?? '∞') . '.');
            }

            if ($downPayment < 0 || $downPayment > $principal) {
                throw new InvalidArgumentException('Down payment must be between 0 and the financed amount.');
            }

            $calc = $this->calculationService->calculate($principal, $plan);
            $financedAmount = $calc['total_payable'] - $downPayment;

            $contract = EmiContract::create([
                'sale_id'            => $sale->id,
                'customer_id'        => $sale->customer_id,
                'emi_bank_id'        => $bank->id,
                'emi_plan_id'        => $plan->id,
                'principal_amount'   => $principal,
                'interest_amount'    => $calc['interest_amount'],
                'fee_amount'         => $calc['fee_amount'],
                'total_payable'      => $calc['total_payable'],
                'down_payment'       => $downPayment,
                'financed_amount'    => $financedAmount,
                'installment_count'  => $plan->duration_months,
                'start_date'         => $startDate,
                'status'             => EmiContractStatus::Active,
                'user_id'            => $userId,
            ]);

            $count = $plan->duration_months;
            $base  = round($financedAmount / $count, 2);
            $running = 0.0;

            for ($i = 1; $i <= $count; $i++) {
                $amount = $i < $count ? $base : round($financedAmount - $running, 2);
                $running += $amount;

                EmiInstallment::create([
                    'emi_contract_id'     => $contract->id,
                    'installment_number' => $i,
                    'due_date'            => Carbon::parse($startDate)->addMonths($i),
                    'amount'              => $amount,
                    'remaining_amount'    => $amount,
                    'status'              => EmiInstallmentStatus::Pending,
                ]);
            }

            if ($downPayment > 0) {
                $this->customerLedgerService->record(
                    customer: $sale->customer,
                    type: CustomerTransactionType::Payment,
                    amount: -$downPayment,
                    referenceType: EmiContract::class,
                    referenceId: $contract->id,
                    userId: $userId,
                    notes: "EMI down payment for sale {$sale->invoice_number}"
                );

                $paidAmount = (float) $sale->paid_amount + $downPayment;
                $sale->update([
                    'paid_amount'    => $paidAmount,
                    'due_amount'     => max((float) $sale->grand_total - $paidAmount, 0),
                    'payment_status' => PaymentStatus::fromAmounts((float) $sale->grand_total, $paidAmount),
                ]);
            }

            return $contract;
        });
    }

    public function cancel(EmiContract $contract): EmiContract
    {
        return DB::transaction(function () use ($contract) {
            $hasPayments = $contract->installments()->where('paid_amount', '>', 0)->exists();

            if ($hasPayments) {
                throw new InvalidArgumentException('Cannot cancel an EMI contract that already has payments — see Return/adjustment workflow instead.');
            }

            $contract->update(['status' => EmiContractStatus::Cancelled]);
            $contract->installments()->update(['status' => EmiInstallmentStatus::Cancelled]);

            return $contract;
        });
    }
}
