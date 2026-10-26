<?php

namespace ME\Retail\Enums;

enum DiscountRuleType: string
{
    case Product  = 'product';
    case Category = 'category';
    case Brand    = 'brand';
    case Quantity = 'quantity';
    case Coupon   = 'coupon';

    public function label(): string
    {
        return match ($this) {
            self::Product  => 'Product',
            self::Category => 'Category',
            self::Brand    => 'Brand',
            self::Quantity => 'Quantity Break',
            self::Coupon   => 'Coupon',
        };
    }
}
