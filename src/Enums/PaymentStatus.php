<?php

namespace ME\Retail\Enums;

enum PaymentStatus: string
{
    case Unpaid  = 'unpaid';
    case Partial = 'partial';
    case Paid    = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid  => 'Unpaid',
            self::Partial => 'Partial',
            self::Paid    => 'Paid',
        };
    }

    public static function fromAmounts(float $grandTotal, float $paidAmount): self
    {
        if ($paidAmount <= 0) {
            return self::Unpaid;
        }

        return $paidAmount >= $grandTotal ? self::Paid : self::Partial;
    }
}
