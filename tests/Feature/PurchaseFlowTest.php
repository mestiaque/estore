<?php

namespace ME\Retail\Tests\Feature;

use ME\Retail\Models\Product;
use ME\Retail\Models\Purchase;
use ME\Retail\Tests\TestCase;

class PurchaseFlowTest extends TestCase
{
    use CreatesCatalog;

    public function test_purchase_increases_stock_and_records_ledger_and_supplier_balance(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $product   = $this->createProduct(['purchase_price' => 0, 'stock_quantity' => 0]);

        $response = $this->actingAs($this->admin())->post(route('retail.purchases.store'), [
            'supplier_id'   => $supplier->id,
            'store_id'      => $store->id,
            'warehouse_id'  => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'paid_amount'   => 500,
            'payment_method' => 'cash',
            'items' => [
                ['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 10, 'purchase_price' => 60],
            ],
        ]);

        $purchase = Purchase::first();
        $response->assertRedirect(route('retail.purchases.show', $purchase->id));
        $response->assertSessionHas('success');

        $product->refresh();
        $this->assertSame('10.0000', $product->stock_quantity);
        $this->assertSame('60.00', $product->purchase_price, 'forward-looking cost should update to the latest purchase price');

        $this->assertSame('600.00', $purchase->grand_total);
        $this->assertSame('500.00', $purchase->paid_amount);
        $this->assertSame('100.00', $purchase->due_amount);
        $this->assertSame('partial', $purchase->payment_status->value);

        // Supplier owes 600 for the purchase, paid 500 -> due 100.
        $supplier->refresh();
        $this->assertSame(100.0, $supplier->due_balance);

        $this->assertDatabaseHas('retail_stock_ledgers', [
            'product_id'     => $product->id,
            'warehouse_id'   => $warehouse->id,
            'movement_type'  => 'purchase',
            'quantity'       => '10.0000',
            'quantity_after' => '10.0000',
        ]);
    }

    public function test_purchase_rejects_a_unit_that_does_not_belong_to_the_product(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $product   = $this->createProduct();

        // A unit with no relation to the product's base unit (different base_unit_id chain).
        $unrelatedUnit = $this->createUnit(1);

        $this->actingAs($this->admin())->post(route('retail.purchases.store'), [
            'supplier_id'   => $supplier->id,
            'store_id'      => $store->id,
            'warehouse_id'  => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'unit_id' => $unrelatedUnit->id, 'quantity' => 5, 'purchase_price' => 10],
            ],
        ]);

        $this->assertSame(0, Purchase::count(), 'a purchase using an invalid unit for the product must not be created');

        $product->refresh();
        $this->assertSame('0.0000', $product->stock_quantity, 'stock must not change when the purchase is rejected');
    }

    public function test_batch_tracked_product_requires_a_batch_number_and_creates_a_batch(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $product   = $this->createProduct(['track_type' => 'batch', 'has_expiry' => true]);

        // Missing batch_number / expiry_date should be rejected by the service.
        $this->actingAs($this->admin())->post(route('retail.purchases.store'), [
            'supplier_id'   => $supplier->id,
            'store_id'      => $store->id,
            'warehouse_id'  => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 5, 'purchase_price' => 10],
            ],
        ]);
        $this->assertSame(0, Purchase::count());

        // Now with batch_number + expiry_date it should succeed and create a batch.
        $this->actingAs($this->admin())->post(route('retail.purchases.store'), [
            'supplier_id'   => $supplier->id,
            'store_id'      => $store->id,
            'warehouse_id'  => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id'     => $product->id,
                    'unit_id'        => $product->unit_id,
                    'quantity'       => 5,
                    'purchase_price' => 10,
                    'batch_number'   => 'BATCH-A',
                    'expiry_date'    => now()->addYear()->toDateString(),
                ],
            ],
        ]);

        $this->assertSame(1, Purchase::count());
        $this->assertDatabaseHas('retail_batches', [
            'product_id'   => $product->id,
            'batch_number' => 'BATCH-A',
            'quantity'     => '5.0000',
        ]);
    }
}
