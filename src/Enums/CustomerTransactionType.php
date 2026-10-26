<?php

namespace ME\Retail\Enums;

enum CustomerTransactionType: string
{
    case Sale       = 'sale';
    case Payment    = 'payment';
    case SaleReturn = 'sale_return';
    case Adjustment = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::Sale       => 'Sale',
            self::Payment    => 'Payment',
            self::SaleReturn => 'Sale Return',
            self::Adjustment => 'Adjustment',
        };
    }
}
