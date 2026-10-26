<?php

namespace ME\Retail\Enums;

enum LoyaltyTransactionType: string
{
    case Earn       = 'earn';
    case Redeem     = 'redeem';
    case Adjustment = 'adjustment';
    case Expired    = 'expired';
    case Reversal   = 'reversal';

    public function label(): string
    {
        return match ($this) {
            self::Earn       => 'Earned',
            self::Redeem     => 'Redeemed',
            self::Adjustment => 'Adjustment',
            self::Expired    => 'Expired',
            self::Reversal   => 'Reversal',
        };
    }
}
