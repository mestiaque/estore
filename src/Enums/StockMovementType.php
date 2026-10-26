<?php

namespace ME\Retail\Enums;

enum StockMovementType: string
{
    case OpeningStock     = 'opening_stock';
    case Purchase         = 'purchase';
    case PurchaseReturn   = 'purchase_return';
    case Sale             = 'sale';
    case SaleReturn       = 'sale_return';
    case Adjustment       = 'adjustment';
    case Damage           = 'damage';
    case Loss             = 'loss';
    case TransferIn       = 'transfer_in';
    case TransferOut      = 'transfer_out';
    case ManualCorrection = 'manual_correction';

    public function label(): string
    {
        return match ($this) {
            self::OpeningStock     => 'Opening Stock',
            self::Purchase         => 'Purchase',
            self::PurchaseReturn   => 'Purchase Return',
            self::Sale             => 'Sale',
            self::SaleReturn       => 'Sale Return',
            self::Adjustment       => 'Stock Adjustment',
            self::Damage           => 'Damage',
            self::Loss             => 'Loss',
            self::TransferIn       => 'Transfer In',
            self::TransferOut      => 'Transfer Out',
            self::ManualCorrection => 'Manual Correction',
        };
    }
}
