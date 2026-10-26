<?php

namespace ME\Retail\Services;

use InvalidArgumentException;
use ME\Retail\Support\RetailSettings;

/**
 * Keeps invoice rendering decoupled from Sale/Purchase business logic (plan §42):
 * the sale/purchase never knows or cares which template renders it — routes/
 * controllers ask this service for the view name, never hard-code one.
 */
class InvoiceService
{
    public function saleInvoiceView(string $type): string
    {
        if (!in_array($type, (RetailSettings::get('invoice_types') ?: ['a4']), true)) {
            throw new InvalidArgumentException("Unknown invoice type \"{$type}\".");
        }

        return "retail::invoices.sales.{$type}";
    }

    public function defaultSaleInvoiceType(): string
    {
        return RetailSettings::get('default_invoice_type');
    }
}
