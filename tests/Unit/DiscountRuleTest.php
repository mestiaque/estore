<?php

namespace ME\Retail\Tests\Unit;

use ME\Retail\Enums\DiscountValueType;
use ME\Retail\Models\DiscountRule;
use ME\Retail\Tests\TestCase;

class DiscountRuleTest extends TestCase
{
    public function test_percent_discount_is_computed_from_base(): void
    {
        $rule = new DiscountRule(['discount_type' => DiscountValueType::Percent, 'discount_value' => 10]);

        $this->assertSame(50.0, $rule->amountFor(500));
    }

    public function test_flat_discount_is_fixed(): void
    {
        $rule = new DiscountRule(['discount_type' => DiscountValueType::Flat, 'discount_value' => 75]);

        $this->assertSame(75.0, $rule->amountFor(500));
    }

    public function test_discount_never_exceeds_the_base_amount(): void
    {
        // A flat 500 discount on a 200 line must not produce a negative total.
        $rule = new DiscountRule(['discount_type' => DiscountValueType::Flat, 'discount_value' => 500]);

        $this->assertSame(200.0, $rule->amountFor(200));
    }

    public function test_inactive_rule_is_not_currently_active(): void
    {
        $rule = new DiscountRule(['status' => false, 'discount_type' => DiscountValueType::Flat, 'discount_value' => 10]);

        $this->assertFalse($rule->isCurrentlyActive());
    }

    public function test_rule_past_its_end_date_is_not_active(): void
    {
        $rule = new DiscountRule([
            'status'         => true,
            'discount_type'  => DiscountValueType::Flat,
            'discount_value' => 10,
            'ends_at'        => now()->subDay()->toDateString(),
        ]);

        $this->assertFalse($rule->isCurrentlyActive());
    }

    public function test_rule_that_reached_its_usage_cap_is_not_active(): void
    {
        $rule = new DiscountRule([
            'status'         => true,
            'discount_type'  => DiscountValueType::Flat,
            'discount_value' => 10,
            'max_uses'       => 5,
            'used_count'     => 5,
        ]);

        $this->assertFalse($rule->isCurrentlyActive());
    }
}
