<?php

namespace ME\Retail\Enums;

enum SupplierTransactionType: string
{
    case Purchase       = 'purchase';
    case Payment        = 'payment';
    case PurchaseReturn = 'purchase_return';
    case Adjustment     = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::Purchase       => 'Purchase',
            self::Payment        => 'Payment',
            self::PurchaseReturn => 'Purchase Return',
            self::Adjustment     => 'Adjustment',
        };
    }
}
