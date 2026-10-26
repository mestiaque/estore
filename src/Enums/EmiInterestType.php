<?php

namespace ME\Retail\Enums;

enum EmiInterestType: string
{
    case Flat        = 'flat';         // one-time: principal * rate%
    case MonthlyFlat = 'monthly_flat'; // principal * rate% * duration_months

    public function label(): string
    {
        return $this === self::Flat ? 'Flat (one-time)' : 'Monthly Flat';
    }
}
