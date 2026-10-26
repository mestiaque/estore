<?php

namespace ME\Retail\Tests\Unit;

use ME\Retail\Enums\PaymentStatus;
use ME\Retail\Tests\TestCase;

class PaymentStatusTest extends TestCase
{
    public function test_zero_paid_is_unpaid(): void
    {
        $this->assertSame(PaymentStatus::Unpaid, PaymentStatus::fromAmounts(1000, 0));
    }

    public function test_partial_payment_is_partial(): void
    {
        $this->assertSame(PaymentStatus::Partial, PaymentStatus::fromAmounts(1000, 400));
    }

    public function test_full_payment_is_paid(): void
    {
        $this->assertSame(PaymentStatus::Paid, PaymentStatus::fromAmounts(1000, 1000));
    }

    public function test_overpayment_is_still_paid(): void
    {
        $this->assertSame(PaymentStatus::Paid, PaymentStatus::fromAmounts(1000, 1200));
    }
}
