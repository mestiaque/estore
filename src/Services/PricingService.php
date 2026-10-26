<?php

namespace ME\Retail\Services;

use InvalidArgumentException;
use ME\Retail\Enums\DiscountRuleType;
use ME\Retail\Models\Customer;
use ME\Retail\Models\DiscountRule;
use ME\Retail\Models\Product;
use ME\Retail\Support\RetailModules;

/**
 * Centralized pricing/discount resolution (plan §67) — POS/Sale asks this
 * service rather than computing discounts itself. All discount percentages
 * and amounts are configured via retail_discount_rules or the customer's
 * level/membership, never hard-coded.
 */
class PricingService
{
    /**
     * Best matching product/category/brand/quantity-break discount for a line item.
     *
     * @return array{amount: float, rule: ?DiscountRule}
     */
    public function itemDiscount(Product $product, float $quantity, float $lineBase): array
    {
        if (!RetailModules::discountEnabled()) {
            return ['amount' => 0.0, 'rule' => null];
        }

        $rules = DiscountRule::query()
            ->where(function ($q) use ($product, $quantity) {
                $q->where(fn ($q2) => $q2->where('type', DiscountRuleType::Product)->where('target_id', $product->id))
                  ->orWhere(fn ($q2) => $q2->where('type', DiscountRuleType::Category)->where('target_id', $product->category_id))
                  ->when($product->brand_id, fn ($q2) => $q2->orWhere(
                      fn ($q3) => $q3->where('type', DiscountRuleType::Brand)->where('target_id', $product->brand_id)
                  ))
                  ->orWhere(function ($q2) use ($product, $quantity) {
                      $q2->where('type', DiscountRuleType::Quantity)
                         ->where('min_quantity', '<=', $quantity)
                         ->where(fn ($q3) => $q3->whereNull('target_id')->orWhere('target_id', $product->id));
                  });
            })
            ->get()
            ->filter(fn (DiscountRule $rule) => $rule->isCurrentlyActive());

        $best = null;
        $bestAmount = 0.0;

        foreach ($rules as $rule) {
            $amount = $rule->amountFor($lineBase);
            if ($amount > $bestAmount) {
                $bestAmount = $amount;
                $best = $rule;
            }
        }

        return ['amount' => $bestAmount, 'rule' => $best];
    }

    /**
     * Automatic invoice-level discount: membership % + customer-level % (both
     * additive, since a customer can hold both per plan §35) plus a coupon
     * code if supplied — validated for active/date-range/usage-limit here.
     *
     * @return array{amount: float, coupon_rule: ?DiscountRule}
     */
    public function invoiceAutoDiscount(?Customer $customer, float $subtotal, ?string $couponCode): array
    {
        $amount = 0.0;

        if ($customer) {
            if (RetailModules::enabled('membership') && $customer->hasActiveMembership() && $customer->membership) {
                $amount += $subtotal * ((float) $customer->membership->discount_percent / 100);
            }

            if (RetailModules::loyaltyEnabled() && $customer->level) {
                $amount += $subtotal * ((float) $customer->level->discount_percent / 100);
            }
        }

        $couponRule = null;

        if ($couponCode) {
            if (!RetailModules::discountEnabled()) {
                throw new InvalidArgumentException('Coupons are turned off in Retail settings.');
            }

            $couponRule = DiscountRule::query()
                ->where('type', DiscountRuleType::Coupon)
                ->where('coupon_code', $couponCode)
                ->first();

            if (!$couponRule || !$couponRule->isCurrentlyActive()) {
                throw new InvalidArgumentException("Coupon code \"{$couponCode}\" is invalid or no longer active.");
            }

            $amount += $couponRule->amountFor(max($subtotal - $amount, 0));
        }

        return ['amount' => min($amount, $subtotal), 'coupon_rule' => $couponRule];
    }

    public function redeemCoupon(DiscountRule $rule): void
    {
        $rule->increment('used_count');
    }
}
