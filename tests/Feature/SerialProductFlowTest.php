<?php

namespace ME\Retail\Tests\Feature;

use ME\Retail\Models\Purchase;
use ME\Retail\Models\Sale;
use ME\Retail\Models\Serial;
use ME\Retail\Tests\TestCase;

class SerialProductFlowTest extends TestCase
{
    use CreatesCatalog;

    public function test_purchasing_a_serial_product_creates_one_serial_per_unit_and_rejects_duplicates(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $product   = $this->createProduct(['track_type' => 'serial']);

        $this->actingAs($this->admin())->post(route('retail.purchases.store'), [
            'supplier_id' => $supplier->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [[
                'product_id' => $product->id, 'unit_id' => $product->unit_id,
                'quantity' => 2, 'purchase_price' => 500,
                'serial_numbers' => ['SN-001', 'SN-002'],
            ]],
        ]);

        $this->assertSame(1, Purchase::count());
        $this->assertSame(2, Serial::where('product_id', $product->id)->count());
        $this->assertDatabaseHas('retail_serials', ['product_id' => $product->id, 'serial_number' => 'SN-001', 'status' => 'available']);

        // Re-purchasing with a serial number already assigned to this product must be rejected.
        $this->actingAs($this->admin())->post(route('retail.purchases.store'), [
            'supplier_id' => $supplier->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [[
                'product_id' => $product->id, 'unit_id' => $product->unit_id,
                'quantity' => 1, 'purchase_price' => 500,
                'serial_numbers' => ['SN-001'],
            ]],
        ]);

        $this->assertSame(1, Purchase::count(), 'a purchase reusing an existing serial number must not be recorded');
        $this->assertSame(2, Serial::where('product_id', $product->id)->count());
    }

    public function test_selling_a_serial_marks_it_sold_and_prevents_selling_it_twice(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $product   = $this->createProduct(['track_type' => 'serial', 'sale_price' => 1000]);

        $this->actingAs($this->admin())->post(route('retail.purchases.store'), [
            'supplier_id' => $supplier->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [[
                'product_id' => $product->id, 'unit_id' => $product->unit_id,
                'quantity' => 1, 'purchase_price' => 500, 'serial_numbers' => ['IMEI-1'],
            ]],
        ]);

        $serial = Serial::where('product_id', $product->id)->first();

        $this->actingAs($this->admin())->post(route('retail.sales.store'), [
            'store_id' => $store->id, 'warehouse_id' => $warehouse->id, 'sale_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 1, 'sale_price' => 1000, 'serial_ids' => [$serial->id]]],
            'payments' => [['payment_method' => 'cash', 'amount' => 1000]],
        ]);

        $this->assertSame(1, Sale::count());
        $serial->refresh();
        $this->assertSame('sold', $serial->status->value);

        // Selling the same (now sold) serial again must be rejected — no product left with that serial available.
        $this->actingAs($this->admin())->post(route('retail.sales.store'), [
            'store_id' => $store->id, 'warehouse_id' => $warehouse->id, 'sale_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 1, 'sale_price' => 1000, 'serial_ids' => [$serial->id]]],
            'payments' => [['payment_method' => 'cash', 'amount' => 1000]],
        ]);

        $this->assertSame(1, Sale::count(), 'reselling an already-sold serial must be blocked');
    }
}
