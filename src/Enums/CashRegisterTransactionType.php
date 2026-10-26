<?php

namespace ME\Retail\Enums;

enum CashRegisterTransactionType: string
{
    case Sale            = 'sale';
    case Refund          = 'refund';
    case Expense         = 'expense';
    case PurchasePayment = 'purchase_payment';
    case OtherIncome     = 'other_income';
    case Adjustment      = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::Sale            => 'Sale',
            self::Refund          => 'Refund',
            self::Expense         => 'Expense',
            self::PurchasePayment => 'Purchase Payment',
            self::OtherIncome     => 'Other Income',
            self::Adjustment      => 'Adjustment',
        };
    }
}
