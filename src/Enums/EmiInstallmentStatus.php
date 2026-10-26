<?php

namespace ME\Retail\Enums;

enum EmiInstallmentStatus: string
{
    case Pending   = 'pending';
    case Partial   = 'partial';
    case Paid      = 'paid';
    case Overdue   = 'overdue';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Pending',
            self::Partial   => 'Partially Paid',
            self::Paid      => 'Paid',
            self::Overdue   => 'Overdue',
            self::Cancelled => 'Cancelled',
        };
    }
}
