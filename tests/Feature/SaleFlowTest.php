<?php

namespace ME\Retail\Tests\Feature;

use ME\Retail\Models\ProductStock;
use ME\Retail\Models\Sale;
use ME\Retail\Services\PurchaseService;
use ME\Retail\Tests\TestCase;

class SaleFlowTest extends TestCase
{
    use CreatesCatalog;

    private function purchaseStock($product, $warehouse, $supplier, float $quantity): void
    {
        app(PurchaseService::class)->create([
            'supplier_id'   => $supplier->id,
            'store_id'      => $warehouse->store_id,
            'warehouse_id'  => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => $quantity, 'purchase_price' => 50],
            ],
        ], $this->admin()->id);
    }

    public function test_walk_in_sale_must_be_paid_in_full_and_decreases_stock(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $product   = $this->createProduct(['sale_price' => 80]);

        $this->purchaseStock($product, $warehouse, $supplier, 20);

        // Underpaid walk-in sale must be rejected.
        $this->actingAs($this->admin())->post(route('retail.sales.store'), [
            'store_id'     => $store->id,
            'warehouse_id' => $warehouse->id,
            'sale_date'    => now()->toDateString(),
            'items'        => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 3, 'sale_price' => 80]],
            'payments'     => [['payment_method' => 'cash', 'amount' => 100]],
        ]);
        $this->assertSame(0, Sale::count(), 'an underpaid walk-in sale must not be created');

        // Fully paid walk-in sale succeeds.
        $response = $this->actingAs($this->admin())->post(route('retail.sales.store'), [
            'store_id'     => $store->id,
            'warehouse_id' => $warehouse->id,
            'sale_date'    => now()->toDateString(),
            'items'        => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 3, 'sale_price' => 80]],
            'payments'     => [['payment_method' => 'cash', 'amount' => 240]],
        ]);

        $sale = Sale::first();
        $response->assertRedirect(route('retail.sales.show', $sale->id));
        $this->assertSame('paid', $sale->payment_status->value);
        $this->assertSame('240.00', $sale->grand_total);

        $product->refresh();
        $this->assertSame('17.0000', $product->stock_quantity);

        $summary = ProductStock::where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->first();
        $this->assertSame('17.0000', $summary->quantity);
    }

    public function test_sale_cannot_oversell_available_stock(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $product   = $this->createProduct(['sale_price' => 80]);

        $this->purchaseStock($product, $warehouse, $supplier, 5);

        $this->actingAs($this->admin())->post(route('retail.sales.store'), [
            'store_id'     => $store->id,
            'warehouse_id' => $warehouse->id,
            'sale_date'    => now()->toDateString(),
            'items'        => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 10, 'sale_price' => 80]],
            'payments'     => [['payment_method' => 'cash', 'amount' => 800]],
        ]);

        $this->assertSame(0, Sale::count(), 'overselling beyond available stock must be blocked');

        $product->refresh();
        $this->assertSame('5.0000', $product->stock_quantity, 'stock must be unchanged after a rejected oversell');
    }

    public function test_decimal_quantity_sale_of_a_fractional_product(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $product   = $this->createProduct(['sale_price' => 100]); // e.g. rice priced per KG

        $this->purchaseStock($product, $warehouse, $supplier, 10);

        $this->actingAs($this->admin())->post(route('retail.sales.store'), [
            'store_id'     => $store->id,
            'warehouse_id' => $warehouse->id,
            'sale_date'    => now()->toDateString(),
            'items'        => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 1.5, 'sale_price' => 100]],
            'payments'     => [['payment_method' => 'cash', 'amount' => 150]],
        ]);

        $product->refresh();
        $this->assertSame('8.5000', $product->stock_quantity);
    }

    public function test_credit_sale_to_a_registered_customer_records_customer_due(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $customer  = $this->createCustomer();
        $product   = $this->createProduct(['sale_price' => 80]);

        $this->purchaseStock($product, $warehouse, $supplier, 10);

        $this->actingAs($this->admin())->post(route('retail.sales.store'), [
            'customer_id'  => $customer->id,
            'store_id'     => $store->id,
            'warehouse_id' => $warehouse->id,
            'sale_date'    => now()->toDateString(),
            'items'        => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 2, 'sale_price' => 80]],
            // No payment at all — a registered customer may buy fully on credit.
        ]);

        $sale = Sale::first();
        $this->assertNotNull($sale);
        $this->assertSame('unpaid', $sale->payment_status->value);
        $this->assertSame('160.00', $sale->due_amount);

        $customer->refresh();
        $this->assertSame(160.0, $customer->due_balance);
    }
}
