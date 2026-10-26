<?php

namespace ME\Retail\Http\Controllers;

use ME\Http\Controllers\Controller;
use ME\Retail\Models\Purchase;
use ME\Retail\Models\Sale;
use ME\Retail\Services\InvoiceService;

class InvoiceController extends Controller
{
    public function __construct(protected InvoiceService $invoiceService)
    {
        $this->middleware('authorization:retail_sale.view')->only('sale');
        $this->middleware('authorization:retail_purchase.view')->only('purchase');
    }

    public function sale(Sale $sale, ?string $type = null)
    {
        $sale->load([
            'customer', 'store', 'warehouse', 'cashier',
            'items.product', 'items.unit', 'items.batch', 'items.serials',
            'payments',
        ]);

        $view = $this->invoiceService->saleInvoiceView($type ?: $this->invoiceService->defaultSaleInvoiceType());

        return view($view, [
            'sale'       => $sale,
            'printType'  => 'invoice',
            'printTitle' => 'INVOICE',
            'printQr'    => $sale->invoice_number,
        ]);
    }

    public function purchase(Purchase $purchase)
    {
        $purchase->load(['supplier', 'store', 'warehouse', 'items.product', 'items.unit', 'items.batch', 'items.serials', 'payments']);

        return view('retail::invoices.purchases.a4', [
            'purchase'   => $purchase,
            'printType'  => 'invoice',
            'printTitle' => 'PURCHASE INVOICE',
            'printQr'    => $purchase->invoice_number,
        ]);
    }
}
