<?php

namespace ME\Retail\Services;

use ME\Retail\Enums\EmiFeeType;
use ME\Retail\Enums\EmiInterestType;
use ME\Retail\Models\EmiPlan;

/**
 * EMI calculation strategy (plan §37) — the only place a plan's interest/fee
 * formula is read, so a shop with a different bank formula swaps this class
 * rather than the EMI amount being hard-coded (never `$emi = $price / 12`).
 *
 * @return array{interest_amount: float, fee_amount: float, total_payable: float}
 */
class EmiCalculationService
{
    public function calculate(float $principal, EmiPlan $plan): array
    {
        $rate = (float) $plan->interest_rate;

        $interest = match ($plan->interest_type) {
            EmiInterestType::Flat        => $principal * ($rate / 100),
            EmiInterestType::MonthlyFlat => $principal * ($rate / 100) * $plan->duration_months,
        };

        $fee = $plan->fee_type === EmiFeeType::Flat
            ? (float) $plan->fee_value
            : $principal * ((float) $plan->fee_value / 100);

        return [
            'interest_amount' => round($interest, 2),
            'fee_amount'      => round($fee, 2),
            'total_payable'   => round($principal + $interest + $fee, 2),
        ];
    }
}
