<?php

namespace ME\Retail\Tests\Unit;

use ME\Retail\Enums\EmiFeeType;
use ME\Retail\Enums\EmiInterestType;
use ME\Retail\Models\EmiPlan;
use ME\Retail\Services\EmiCalculationService;
use ME\Retail\Tests\TestCase;

class EmiCalculationServiceTest extends TestCase
{
    public function test_flat_interest_is_one_time_regardless_of_duration(): void
    {
        $plan = new EmiPlan([
            'duration_months' => 12,
            'interest_type'   => EmiInterestType::Flat,
            'interest_rate'   => 10, // 10%
            'fee_type'        => EmiFeeType::Flat,
            'fee_value'       => 0,
        ]);

        $result = (new EmiCalculationService())->calculate(10000, $plan);

        $this->assertSame(1000.0, $result['interest_amount']);
        $this->assertSame(11000.0, $result['total_payable']);
    }

    public function test_monthly_flat_interest_multiplies_by_duration(): void
    {
        $plan = new EmiPlan([
            'duration_months' => 6,
            'interest_type'   => EmiInterestType::MonthlyFlat,
            'interest_rate'   => 2, // 2% per month
            'fee_type'        => EmiFeeType::Flat,
            'fee_value'       => 0,
        ]);

        $result = (new EmiCalculationService())->calculate(10000, $plan);

        // 10000 * 2% * 6 months = 1200
        $this->assertSame(1200.0, $result['interest_amount']);
        $this->assertSame(11200.0, $result['total_payable']);
    }

    public function test_percentage_fee_is_based_on_principal(): void
    {
        $plan = new EmiPlan([
            'duration_months' => 3,
            'interest_type'   => EmiInterestType::Flat,
            'interest_rate'   => 0,
            'fee_type'        => EmiFeeType::Percentage,
            'fee_value'       => 5, // 5%
        ]);

        $result = (new EmiCalculationService())->calculate(2000, $plan);

        $this->assertSame(100.0, $result['fee_amount']);
        $this->assertSame(2100.0, $result['total_payable']);
    }
}
