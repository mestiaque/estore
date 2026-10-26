<?php

namespace ME\Retail\Enums;

enum EmiFeeType: string
{
    case Flat       = 'flat';
    case Percentage = 'percentage';

    public function label(): string
    {
        return $this === self::Flat ? 'Flat Amount' : 'Percentage';
    }
}
