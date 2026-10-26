<?php

namespace ME\Retail\Tests\Feature;

use ME\Retail\Services\SaleService;
use ME\Retail\Services\PurchaseService;
use ME\Retail\Tests\TestCase;

/**
 * These forms build their client-side product/unit/batch/serial data via
 * @json($collection->map(fn($x) => [...])) in Blade. Blade's @json() splits
 * its argument on every top-level comma, so an inline multi-key array
 * literal silently truncates the expression at compile time — the page
 * would 500 the moment a real browser loaded it. Rendering each one here
 * catches that class of bug, which unit/flow tests that only POST never would.
 */
class FormsRenderTest extends TestCase
{
    use CreatesCatalog;

    public function test_sale_create_form_renders(): void
    {
        $store     = $this->createStore();
        $this->createWarehouse($store);
        $this->createProduct();

        $response = $this->actingAs($this->admin())->get(route('retail.sales.create'));
        $response->assertOk();
        $response->assertSee('retailProducts', false);
    }

    public function test_purchase_create_form_renders(): void
    {
        $store = $this->createStore();
        $this->createWarehouse($store);
        $this->createSupplier();
        $this->createProduct();

        $response = $this->actingAs($this->admin())->get(route('retail.purchases.create'));
        $response->assertOk();
        $response->assertSee('retailProducts', false);
    }

    public function test_stock_transfer_create_form_renders(): void
    {
        $store = $this->createStore();
        $this->createWarehouse($store);
        $this->createProduct();

        $response = $this->actingAs($this->admin())->get(route('retail.stock-transfers.create'));
        $response->assertOk();
        $response->assertSee('retailProducts', false);
    }

    public function test_emi_contract_create_form_renders(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $customer  = $this->createCustomer();
        $product   = $this->createProduct(['sale_price' => 5000]);

        app(PurchaseService::class)->create([
            'supplier_id' => $supplier->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 5, 'purchase_price' => 50]],
        ], $this->admin()->id);

        $sale = app(SaleService::class)->create([
            'customer_id' => $customer->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'sale_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 1, 'sale_price' => 5000]],
        ], $this->admin()->id);

        $response = $this->actingAs($this->admin())->get(route('retail.emi-contracts.create', $sale->id));
        $response->assertOk();
        $response->assertSee('banks', false);
    }

    public function test_sale_invoice_prints_render_with_header_logo_and_qr(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $product   = $this->createProduct(['sale_price' => 100]);

        app(PurchaseService::class)->create([
            'supplier_id' => $supplier->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 5, 'purchase_price' => 50]],
        ], $this->admin()->id);

        $sale = app(SaleService::class)->create([
            'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'sale_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 1, 'sale_price' => 100]],
            'payments' => [['payment_method' => 'cash', 'amount' => 100]],
        ], $this->admin()->id);

        foreach (['a4', 'thermal80', 'thermal58'] as $type) {
            $response = $this->actingAs($this->admin())->get(route('retail.sales.invoice', [$sale->id, $type]));
            $response->assertOk();
            $response->assertSee('header-logo', false);
            $response->assertSee('header-qr', false);
            $response->assertSee('QRCode', false);
        }
    }

    public function test_purchase_invoice_prints_with_header_logo_and_qr(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $product   = $this->createProduct();

        $purchase = app(PurchaseService::class)->create([
            'supplier_id' => $supplier->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 5, 'purchase_price' => 50]],
        ], $this->admin()->id);

        $response = $this->actingAs($this->admin())->get(route('retail.purchases.invoice', $purchase->id));
        $response->assertOk();
        $response->assertSee('header-logo', false);
        $response->assertSee('header-qr', false);
    }
}
