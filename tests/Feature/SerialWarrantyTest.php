<?php

namespace ME\Retail\Tests\Feature;

use ME\Retail\Models\Serial;
use ME\Retail\Services\PurchaseService;
use ME\Retail\Services\SaleService;
use ME\Retail\Tests\TestCase;

class SerialWarrantyTest extends TestCase
{
    use CreatesCatalog;

    public function test_warranty_months_entered_at_purchase_sets_serial_warranty_dates(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $product   = $this->createProduct(['track_type' => 'serial', 'sale_price' => 20000]);

        app(PurchaseService::class)->create([
            'supplier_id' => $supplier->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => '2026-01-10',
            'items' => [[
                'product_id' => $product->id, 'unit_id' => $product->unit_id,
                'quantity' => 1, 'purchase_price' => 15000,
                'serial_numbers' => ['IMEI-WARR-1'], 'warranty_months' => 12,
            ]],
        ], $this->admin()->id);

        $serial = Serial::where('serial_number', 'IMEI-WARR-1')->first();

        $this->assertSame(12, $serial->warranty_months);
        $this->assertSame('2026-01-10', $serial->warranty_start->toDateString());
        $this->assertSame('2027-01-10', $serial->warranty_end->toDateString());
    }

    public function test_purchase_without_warranty_input_leaves_serial_warranty_null(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $product   = $this->createProduct(['track_type' => 'serial']);

        app(PurchaseService::class)->create([
            'supplier_id' => $supplier->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [[
                'product_id' => $product->id, 'unit_id' => $product->unit_id,
                'quantity' => 1, 'purchase_price' => 500, 'serial_numbers' => ['SN-NOWARR-1'],
            ]],
        ], $this->admin()->id);

        $serial = Serial::where('serial_number', 'SN-NOWARR-1')->first();

        $this->assertNull($serial->warranty_months);
        $this->assertNull($serial->warranty_end);
    }

    public function test_sale_invoice_and_sale_show_display_the_serials_warranty(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $product   = $this->createProduct(['track_type' => 'serial', 'sale_price' => 20000]);

        app(PurchaseService::class)->create([
            'supplier_id' => $supplier->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => '2026-01-10',
            'items' => [[
                'product_id' => $product->id, 'unit_id' => $product->unit_id,
                'quantity' => 1, 'purchase_price' => 15000,
                'serial_numbers' => ['IMEI-WARR-2'], 'warranty_months' => 6,
            ]],
        ], $this->admin()->id);

        $serial = Serial::where('serial_number', 'IMEI-WARR-2')->first();

        $sale = app(SaleService::class)->create([
            'store_id' => $store->id, 'warehouse_id' => $warehouse->id, 'sale_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 1, 'sale_price' => 20000, 'serial_ids' => [$serial->id]]],
            'payments' => [['payment_method' => 'cash', 'amount' => 20000]],
        ], $this->admin()->id);

        $expectedDate = '10 Jul 2026';

        $show = $this->actingAs($this->admin())->get(route('retail.sales.show', $sale->id));
        $show->assertOk();
        $show->assertSee($expectedDate);

        $invoice = $this->actingAs($this->admin())->get(route('retail.sales.invoice', [$sale->id, 'a4']));
        $invoice->assertOk();
        $invoice->assertSee($expectedDate);

        $thermal = $this->actingAs($this->admin())->get(route('retail.sales.invoice', [$sale->id, 'thermal58']));
        $thermal->assertOk();
        $thermal->assertSee($expectedDate);
    }

    public function test_purchase_create_form_has_warranty_input_for_serial_products(): void
    {
        $response = $this->actingAs($this->admin())->get(route('retail.purchases.create'));
        $response->assertOk();
        $response->assertSee('warranty_months', false);
    }
}
