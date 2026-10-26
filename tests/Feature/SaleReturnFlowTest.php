<?php

namespace ME\Retail\Tests\Feature;

use ME\Retail\Models\Sale;
use ME\Retail\Models\SaleReturn;
use ME\Retail\Services\PurchaseService;
use ME\Retail\Services\SaleService;
use ME\Retail\Tests\TestCase;

class SaleReturnFlowTest extends TestCase
{
    use CreatesCatalog;

    public function test_partial_return_restocks_and_blocks_returning_more_than_was_sold(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $customer  = $this->createCustomer();
        $product   = $this->createProduct(['sale_price' => 80]);

        app(PurchaseService::class)->create([
            'supplier_id' => $supplier->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 10, 'purchase_price' => 50]],
        ], $this->admin()->id);

        $sale = app(SaleService::class)->create([
            'customer_id' => $customer->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'sale_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 10, 'sale_price' => 80]],
            'payments' => [['payment_method' => 'cash', 'amount' => 800]],
        ], $this->admin()->id);

        $saleItem = $sale->items()->first();

        // Return 2 of the 10 sold.
        $response = $this->actingAs($this->admin())->post(route('retail.sales.returns.store', $sale->id), [
            'items' => [['sale_item_id' => $saleItem->id, 'quantity' => 2]],
        ]);

        $saleReturn = SaleReturn::first();
        $response->assertRedirect(route('retail.sales.returns.show', $saleReturn->id));
        $this->assertSame('160.00', $saleReturn->total_refund); // 2 units at the sale's effective per-unit rate

        $product->refresh();
        $this->assertSame('2.0000', $product->stock_quantity, '10 purchased - 10 sold + 2 returned = 2');

        // Attempting to return the remaining 9 (only 8 are left returnable) must be rejected.
        $this->actingAs($this->admin())->post(route('retail.sales.returns.store', $sale->id), [
            'items' => [['sale_item_id' => $saleItem->id, 'quantity' => 9]],
        ]);

        $this->assertSame(1, SaleReturn::count(), 'an over-return must not create a second return record');

        $product->refresh();
        $this->assertSame('2.0000', $product->stock_quantity, 'stock must be unchanged after a rejected over-return');
    }

    public function test_return_reverses_points_earned_on_the_original_sale(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $customer  = $this->createCustomer();
        $product   = $this->createProduct(['sale_price' => 80, 'earn_points' => 5]);

        app(PurchaseService::class)->create([
            'supplier_id' => $supplier->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 10, 'purchase_price' => 50]],
        ], $this->admin()->id);

        $sale = app(SaleService::class)->create([
            'customer_id' => $customer->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'sale_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 4, 'sale_price' => 80]],
            'payments' => [['payment_method' => 'cash', 'amount' => 320]],
        ], $this->admin()->id);

        $customer->refresh();
        $this->assertSame(20.0, $customer->points_balance); // 4 units * 5 points

        $saleItem = $sale->items()->first();

        $this->actingAs($this->admin())->post(route('retail.sales.returns.store', $sale->id), [
            'items' => [['sale_item_id' => $saleItem->id, 'quantity' => 2]],
        ]);

        $customer->refresh();
        $this->assertSame(10.0, $customer->points_balance, 'returning half the units should reverse half the points earned');
    }
}
