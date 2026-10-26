<?php

namespace ME\Retail\Tests\Feature;

use ME\Retail\Models\StockAdjustment;
use ME\Retail\Services\PurchaseService;
use ME\Retail\Tests\TestCase;

class StockAdjustmentFlowTest extends TestCase
{
    use CreatesCatalog;

    public function test_negative_adjustment_reduces_stock_and_is_audited(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $product   = $this->createProduct();

        app(PurchaseService::class)->create([
            'supplier_id' => $supplier->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 20, 'purchase_price' => 50]],
        ], $this->admin()->id);

        $response = $this->actingAs($this->admin())->post(route('retail.stock-adjustments.store'), [
            'product_id'        => $product->id,
            'warehouse_id'      => $warehouse->id,
            'quantity_adjusted' => -3,
            'reason'            => 'damage',
            'notes'             => 'Dropped during unloading',
        ]);

        $adjustment = StockAdjustment::first();
        $response->assertRedirect(route('retail.stock-adjustments.index'));
        $this->assertSame('20.0000', $adjustment->quantity_before);
        $this->assertSame('-3.0000', $adjustment->quantity_adjusted);
        $this->assertSame('17.0000', $adjustment->quantity_after);
        $this->assertSame('damage', $adjustment->reason->value);

        $product->refresh();
        $this->assertSame('17.0000', $product->stock_quantity);
    }

    public function test_adjustment_below_zero_is_blocked_when_negative_stock_is_disallowed(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $product   = $this->createProduct(['stock_quantity' => 0]);

        // No purchase made — product has zero stock. config('retail.allow_negative_stock') defaults false.
        $this->actingAs($this->admin())->post(route('retail.stock-adjustments.store'), [
            'product_id'        => $product->id,
            'warehouse_id'      => $warehouse->id,
            'quantity_adjusted' => -5,
            'reason'            => 'lost',
        ]);

        $this->assertSame(0, StockAdjustment::count(), 'an adjustment that would drive stock negative must be rejected');

        $product->refresh();
        $this->assertSame('0.0000', $product->stock_quantity);
    }
}
