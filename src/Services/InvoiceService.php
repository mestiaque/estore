<?php

namespace ME\Retail\Services;

use InvalidArgumentException;

/**
 * Keeps invoice rendering decoupled from Sale/Purchase business logic (plan §42):
 * the sale/purchase never knows or cares which template renders it — routes/
 * controllers ask this service for the view name, never hard-code one.
 */
class InvoiceService
{
    public function saleInvoiceView(string $type): string
    {
        if (!in_array($type, config('retail.invoice_types', ['a4', 'thermal80', 'thermal58']), true)) {
            throw new InvalidArgumentException("Unknown invoice type \"{$type}\".");
        }

        return "retail::invoices.sales.{$type}";
    }

    public function defaultSaleInvoiceType(): string
    {
        return config('retail.default_invoice_type', 'a4');
    }
}
