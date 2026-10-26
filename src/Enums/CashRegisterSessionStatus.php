<?php

namespace ME\Retail\Enums;

enum CashRegisterSessionStatus: string
{
    case Open   = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return $this === self::Open ? 'Open' : 'Closed';
    }
}
