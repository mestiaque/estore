<?php

namespace ME\Retail\Enums;

enum SerialStatus: string
{
    case Available          = 'available';
    case Reserved           = 'reserved';
    case Sold               = 'sold';
    case Returned           = 'returned';
    case Warranty           = 'warranty';
    case Damaged            = 'damaged';
    case Lost                = 'lost';
    case ReturnedToSupplier = 'returned_to_supplier';

    public function label(): string
    {
        return match ($this) {
            self::Available          => 'Available',
            self::Reserved           => 'Reserved',
            self::Sold               => 'Sold',
            self::Returned           => 'Returned',
            self::Warranty           => 'Warranty / Service',
            self::Damaged            => 'Damaged',
            self::Lost               => 'Lost',
            self::ReturnedToSupplier => 'Returned to Supplier',
        };
    }
}
