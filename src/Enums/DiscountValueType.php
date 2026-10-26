<?php

namespace ME\Retail\Enums;

enum DiscountValueType: string
{
    case Percent = 'percent';
    case Flat    = 'flat';

    public function label(): string
    {
        return $this === self::Percent ? 'Percent' : 'Flat Amount';
    }
}
