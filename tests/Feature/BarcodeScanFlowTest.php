<?php

namespace ME\Retail\Tests\Feature;

use ME\Models\Setting;
use ME\Retail\Models\Batch;
use ME\Retail\Models\Purchase;
use ME\Retail\Models\Warehouse;
use ME\Retail\Services\PurchaseService;
use ME\Retail\Services\StockTransferService;
use ME\Retail\Tests\TestCase;

class BarcodeScanFlowTest extends TestCase
{
    use CreatesCatalog;

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function receive(Warehouse $warehouse, array $items): void
    {
        app(PurchaseService::class)->create([
            'supplier_id'   => $this->createSupplier()->id,
            'store_id'      => $warehouse->store_id,
            'warehouse_id'  => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items'         => $items,
        ], $this->admin()->id);
    }

    private function batchItem($product, string $batchNumber, float $quantity, ?string $expiry = null, ?string $barcode = null): array
    {
        return [
            'product_id' => $product->id, 'unit_id' => $product->unit_id,
            'quantity' => $quantity, 'purchase_price' => 20,
            'batch_number' => $batchNumber, 'expiry_date' => $expiry, 'batch_barcode' => $barcode,
        ];
    }

    private function lookup(string $code, string $context, ?int $warehouseId = null)
    {
        return $this->actingAs($this->admin())->getJson(route('retail.barcode.lookup', array_filter([
            'code' => $code, 'context' => $context, 'warehouse_id' => $warehouseId,
        ])));
    }

    public function test_new_lot_without_supplier_barcode_gets_a_generated_lot_barcode(): void
    {
        $warehouse = $this->createWarehouse($this->createStore());
        $product   = $this->createProduct(['track_type' => 'batch']);

        $this->receive($warehouse, [$this->batchItem($product, 'B-1', 10)]);

        $batch = Batch::where('batch_number', 'B-1')->first();
        $this->assertSame('LOT-' . str_pad((string) $batch->id, 6, '0', STR_PAD_LEFT), $batch->barcode);
    }

    public function test_supplier_lot_barcode_is_kept_and_reused_on_the_next_purchase_of_the_same_lot(): void
    {
        $warehouse = $this->createWarehouse($this->createStore());
        $product   = $this->createProduct(['track_type' => 'batch']);

        $this->receive($warehouse, [$this->batchItem($product, 'B-1', 10, null, '8901234567890')]);
        $this->receive($warehouse, [$this->batchItem($product, 'B-1', 5)]);

        $this->assertSame(1, Batch::count());
        $batch = Batch::first();
        $this->assertSame('8901234567890', $batch->barcode);
        $this->assertSame('15.0000', $batch->quantity);
    }

    public function test_lot_barcode_already_used_by_a_product_is_rejected(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $this->createProduct(['barcode' => 'TAKEN-1']);
        $product   = $this->createProduct(['track_type' => 'batch']);

        $this->actingAs($this->admin())->post(route('retail.purchases.store'), [
            'supplier_id' => $this->createSupplier()->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [$this->batchItem($product, 'B-1', 10, null, 'TAKEN-1')],
        ])->assertSessionHas('error');

        $this->assertSame(0, Purchase::count(), 'a purchase whose lot barcode clashes with a product barcode must roll back');
        $this->assertSame(0, Batch::count());
    }

    public function test_lot_barcode_is_not_generated_when_auto_generation_is_off(): void
    {
        Setting::set('retail_auto_generate_batch_barcode', false);

        $warehouse = $this->createWarehouse($this->createStore());
        $product   = $this->createProduct(['track_type' => 'batch']);

        $this->receive($warehouse, [$this->batchItem($product, 'B-1', 10)]);

        $this->assertNull(Batch::first()->barcode);
    }

    public function test_transferred_lot_keeps_its_barcode_in_the_destination_warehouse(): void
    {
        $store = $this->createStore();
        $from  = $this->createWarehouse($store);
        $to    = $this->createWarehouse($store);
        $product = $this->createProduct(['track_type' => 'batch']);

        $this->receive($from, [$this->batchItem($product, 'B-1', 10, null, 'LOT-SUPPLIER-9')]);
        $source = Batch::first();

        app(StockTransferService::class)->create([
            'from_warehouse_id' => $from->id, 'to_warehouse_id' => $to->id, 'transfer_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 4, 'source_batch_id' => $source->id]],
        ], $this->admin()->id);

        $destination = Batch::where('warehouse_id', $to->id)->first();
        $this->assertSame('LOT-SUPPLIER-9', $destination->barcode);

        // In the sale context the same code resolves to the selling warehouse's own row of the lot.
        $this->lookup('LOT-SUPPLIER-9', 'sale', $to->id)->assertOk()->assertJsonPath('batch.id', $destination->id);
    }

    public function test_sale_lookup_resolves_product_barcode_sku_and_serial(): void
    {
        $warehouse = $this->createWarehouse($this->createStore());
        $normal    = $this->createProduct(['barcode' => '1111', 'sku' => 'SKU-NORMAL']);
        $phone     = $this->createProduct(['track_type' => 'serial', 'sku' => 'SKU-PHONE']);

        $this->receive($warehouse, [
            ['product_id' => $normal->id, 'unit_id' => $normal->unit_id, 'quantity' => 5, 'purchase_price' => 10],
            ['product_id' => $phone->id, 'unit_id' => $phone->unit_id, 'quantity' => 1, 'purchase_price' => 500, 'serial_numbers' => ['IMEI-123']],
        ]);

        $this->lookup('1111', 'sale', $warehouse->id)->assertOk()
            ->assertJsonPath('match', 'product')
            ->assertJsonPath('product.id', $normal->id);

        $this->lookup('SKU-NORMAL', 'sale', $warehouse->id)->assertOk()
            ->assertJsonPath('match', 'sku')
            ->assertJsonPath('product.id', $normal->id);

        $this->lookup('IMEI-123', 'sale', $warehouse->id)->assertOk()
            ->assertJsonPath('match', 'serial')
            ->assertJsonPath('product.id', $phone->id)
            ->assertJsonPath('serial.serial_number', 'IMEI-123');

        $this->lookup('NO-SUCH-CODE', 'sale', $warehouse->id)->assertNotFound()->assertJsonPath('found', false);
    }

    public function test_sale_lookup_of_a_batch_product_barcode_picks_the_earliest_expiring_lot_with_stock(): void
    {
        $warehouse = $this->createWarehouse($this->createStore());
        $product   = $this->createProduct(['track_type' => 'batch', 'has_expiry' => true, 'barcode' => 'MILK-1']);

        $this->receive($warehouse, [
            $this->batchItem($product, 'LATE', 10, now()->addMonths(6)->toDateString()),
            $this->batchItem($product, 'EARLY', 10, now()->addMonth()->toDateString()),
        ]);
        $early = Batch::where('batch_number', 'EARLY')->first();
        $late  = Batch::where('batch_number', 'LATE')->first();

        $this->lookup('MILK-1', 'sale', $warehouse->id)->assertOk()
            ->assertJsonPath('match', 'product')
            ->assertJsonPath('batch.id', $early->id);

        // Scanning a specific lot's barcode sells that lot, even if it expires later.
        $this->lookup($late->barcode, 'sale', $warehouse->id)->assertOk()
            ->assertJsonPath('match', 'batch')
            ->assertJsonPath('batch.id', $late->id);

        // A sold-out lot can't be scanned into a sale, but is still recognised when receiving more of it.
        $early->update(['quantity' => 0]);
        $this->lookup($early->barcode, 'sale', $warehouse->id)->assertNotFound();
        $this->lookup($early->barcode, 'purchase', $warehouse->id)->assertOk()
            ->assertJsonPath('batch.batch_number', 'EARLY');
    }

    public function test_serial_numbers_are_not_matched_when_receiving_stock(): void
    {
        $warehouse = $this->createWarehouse($this->createStore());
        $phone     = $this->createProduct(['track_type' => 'serial']);

        $this->receive($warehouse, [
            ['product_id' => $phone->id, 'unit_id' => $phone->unit_id, 'quantity' => 1, 'purchase_price' => 500, 'serial_numbers' => ['IMEI-555']],
        ]);

        $this->lookup('IMEI-555', 'purchase', $warehouse->id)->assertNotFound();
    }

    public function test_product_barcode_cannot_reuse_a_lot_barcode(): void
    {
        $warehouse = $this->createWarehouse($this->createStore());
        $product   = $this->createProduct(['track_type' => 'batch']);
        $this->receive($warehouse, [$this->batchItem($product, 'B-1', 10, null, 'LOT-ABC')]);

        $this->actingAs($this->admin())->put(route('retail.products.update', $product), [
            'category_id' => $product->category_id, 'unit_id' => $product->unit_id,
            'name' => $product->name, 'sku' => $product->sku, 'barcode' => 'LOT-ABC',
            'purchase_price' => 20, 'sale_price' => 30, 'track_type' => 'batch',
        ])->assertSessionHasErrors('barcode');
    }

    public function test_bulk_generate_fills_missing_lot_barcodes_and_lot_labels_print(): void
    {
        Setting::set('retail_auto_generate_batch_barcode', false);

        $warehouse = $this->createWarehouse($this->createStore());
        $product   = $this->createProduct(['track_type' => 'batch', 'barcode' => 'P-1']);
        $this->receive($warehouse, [$this->batchItem($product, 'B-7', 10)]);
        $batch = Batch::first();
        $this->assertNull($batch->barcode);

        $this->actingAs($this->admin())->post(route('retail.products.barcode.bulk-generate'))->assertSessionHas('success');
        $this->assertSame('LOT-' . str_pad((string) $batch->id, 6, '0', STR_PAD_LEFT), $batch->refresh()->barcode);

        $this->actingAs($this->admin())->get(route('retail.barcode-labels.form'))->assertOk()->assertSee('B-7');

        $this->actingAs($this->admin())->post(route('retail.barcode-labels.print'), [
            'size'    => '40x30',
            'batches' => [['id' => $batch->id, 'quantity' => 2]],
        ])->assertOk()->assertSee('data-code="' . $batch->barcode . '"', false)->assertSee('B-7');
    }

    public function test_sale_and_purchase_screens_have_a_scan_input(): void
    {
        $this->actingAs($this->admin())->get(route('retail.sales.create'))->assertOk()->assertSee('id="barcode-scan"', false);
        $this->actingAs($this->admin())->get(route('retail.purchases.create'))->assertOk()->assertSee('id="barcode-scan"', false);
    }
}
