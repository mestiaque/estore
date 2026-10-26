<?php

namespace ME\Retail\Enums;

enum StockAdjustmentReason: string
{
    case Damage         = 'damage';
    case Lost           = 'lost';
    case Expired        = 'expired';
    case PhysicalCount  = 'physical_count';
    case Correction     = 'correction';
    case OpeningBalance = 'opening_balance';

    public function label(): string
    {
        return match ($this) {
            self::Damage         => 'Damage',
            self::Lost           => 'Lost',
            self::Expired        => 'Expired',
            self::PhysicalCount  => 'Physical Count Mismatch',
            self::Correction     => 'Correction',
            self::OpeningBalance => 'Opening Balance',
        };
    }
}
