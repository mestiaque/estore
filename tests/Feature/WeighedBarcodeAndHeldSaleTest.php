<?php

namespace ME\Retail\Tests\Feature;

use ME\Models\Setting;
use ME\Retail\Models\HeldSale;
use ME\Retail\Models\Sale;
use ME\Retail\Models\Unit;
use ME\Retail\Services\PurchaseService;
use ME\Retail\Services\WeighedBarcodeParser;
use ME\Retail\Tests\TestCase;

class WeighedBarcodeAndHeldSaleTest extends TestCase
{
    use CreatesCatalog;
    use SubmitsRetailSettings;

    /**
     * Builds a scale label: prefix + item code + value + EAN check digit.
     */
    private function scaleLabel(string $prefix, string $itemCode, string $value): string
    {
        $body = $prefix . $itemCode . $value;
        $sum = 0;
        foreach (array_reverse(str_split($body)) as $index => $digit) {
            $sum += (int) $digit * ($index % 2 === 0 ? 3 : 1);
        }

        return $body . (10 - $sum % 10) % 10;
    }

    private function kilogramUnit(): Unit
    {
        return Unit::create(['name' => 'Kilogram', 'short_name' => 'kg', 'conversion_factor' => 1, 'status' => true]);
    }

    private function lookup(string $code, int $warehouseId)
    {
        return $this->actingAs($this->admin())->getJson(route('retail.barcode.lookup', [
            'code' => $code, 'context' => 'sale', 'warehouse_id' => $warehouseId,
        ]));
    }

    public function test_weight_label_resolves_to_the_plu_product_with_its_weight_in_kg(): void
    {
        Setting::set('retail_weighed_barcode_enabled', true);
        $warehouse = $this->createWarehouse($this->createStore());
        $fish = $this->createProduct(['unit_id' => $this->kilogramUnit()->id, 'plu_code' => '00123', 'sale_price' => 600]);

        $this->lookup($this->scaleLabel('21', '00123', '01250'), $warehouse->id)->assertOk()
            ->assertJsonPath('match', 'weighed')
            ->assertJsonPath('product.id', $fish->id)
            ->assertJsonPath('quantity', 1.25);
    }

    public function test_price_label_is_converted_to_quantity_using_the_sale_price(): void
    {
        Setting::set('retail_weighed_barcode_enabled', true);
        Setting::set('retail_weighed_barcode_value_type', 'price');
        $warehouse = $this->createWarehouse($this->createStore());
        // PLU stored without leading zeros still matches the zero-padded item code on the label.
        $beef = $this->createProduct(['unit_id' => $this->kilogramUnit()->id, 'plu_code' => '77', 'sale_price' => 800]);

        // 400.00 taka worth at 800/kg = 0.5 kg.
        $this->lookup($this->scaleLabel('22', '00077', '40000'), $warehouse->id)->assertOk()
            ->assertJsonPath('product.id', $beef->id)
            ->assertJsonPath('quantity', 0.5);
    }

    public function test_scale_labels_are_ignored_when_disabled_or_the_check_digit_is_wrong(): void
    {
        $warehouse = $this->createWarehouse($this->createStore());
        $this->createProduct(['unit_id' => $this->kilogramUnit()->id, 'plu_code' => '00123']);
        $label = $this->scaleLabel('21', '00123', '01250');

        $this->lookup($label, $warehouse->id)->assertNotFound();

        Setting::set('retail_weighed_barcode_enabled', true);
        $badCheckDigit = substr($label, 0, -1) . ((int) substr($label, -1) + 1) % 10;
        $this->lookup($badCheckDigit, $warehouse->id)->assertNotFound();
        $this->lookup($label, $warehouse->id)->assertOk();
    }

    public function test_product_sold_per_gram_gets_the_label_weight_in_grams(): void
    {
        Setting::set('retail_weighed_barcode_enabled', true);
        $gram = Unit::create(['name' => 'Gram', 'short_name' => 'g', 'conversion_factor' => 1, 'status' => true]);
        $saffron = $this->createProduct(['unit_id' => $gram->id, 'plu_code' => '00500']);

        $quantity = app(WeighedBarcodeParser::class)->quantityFor($saffron->load('unit'), 250);

        $this->assertSame(250.0, $quantity);
    }

    public function test_module_settings_save_scale_barcode_options(): void
    {
        Setting::set('retail_weighed_barcode_enabled', true);

        $this->actingAs($this->admin())->put(route('retail.settings.general.update'), [
            'settings' => array_merge($this->currentSettingsPayload(), [
                'weighed_barcode__value_type' => 'price',
                'weighed_barcode__prefixes'   => '20, 28',
            ]),
        ])->assertRedirect(route('retail.settings.general.edit'))->assertSessionHasNoErrors();

        $settings = app(WeighedBarcodeParser::class)->settings();
        $this->assertTrue($settings['enabled']);
        $this->assertSame('price', $settings['value_type']);
        $this->assertSame(['20', '28'], $settings['prefixes']);
    }

    public function test_holding_a_sale_parks_the_cart_without_touching_stock_and_resume_completes_it(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $customer  = $this->createCustomer();
        $product   = $this->createProduct(['sale_price' => 80]);

        app(PurchaseService::class)->create([
            'supplier_id' => $this->createSupplier()->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 10, 'purchase_price' => 50]],
        ], $this->admin()->id);

        // The form's blank trailing line is ignored; only the real line is held.
        $this->actingAs($this->admin())->post(route('retail.sales.hold'), [
            'customer_id' => $customer->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'sale_date' => now()->toDateString(), 'hold_reference' => 'Table 4',
            'items' => [
                ['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 3, 'sale_price' => 80, 'discount' => 0, 'tax' => 0],
                ['product_id' => '', 'unit_id' => '', 'quantity' => ''],
            ],
        ])->assertRedirect(route('retail.sales.create'));

        $held = HeldSale::first();
        $this->assertSame('Table 4', $held->reference);
        $this->assertSame(1, $held->item_count);
        $this->assertSame('240.00', $held->estimated_total);
        $this->assertSame('10.0000', $product->refresh()->stock_quantity, 'holding must not move stock');
        $this->assertSame(0, Sale::count());

        $this->actingAs($this->admin())->get(route('retail.sales.create'))->assertOk()->assertSee('Table 4');
        $this->actingAs($this->admin())->get(route('retail.sales.create', ['resume' => $held->id]))->assertOk()
            ->assertSee('name="held_sale_id" value="' . $held->id . '"', false);

        $this->actingAs($this->admin())->post(route('retail.sales.store'), [
            'customer_id' => $customer->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'sale_date' => now()->toDateString(), 'held_sale_id' => $held->id,
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 3, 'sale_price' => 80]],
            'payments' => [['payment_method' => 'cash', 'amount' => 240]],
        ])->assertRedirect(route('retail.sales.show', Sale::first()->id));

        $this->assertSame(0, HeldSale::count(), 'completing the resumed sale removes it from the held list');
        $this->assertSame('7.0000', $product->refresh()->stock_quantity);
    }

    public function test_holding_an_empty_cart_is_rejected_and_a_held_sale_can_be_discarded(): void
    {
        $this->actingAs($this->admin())->post(route('retail.sales.hold'), [
            'items' => [['product_id' => '']],
        ])->assertSessionHasErrors('items');
        $this->assertSame(0, HeldSale::count());

        $held = HeldSale::create(['payload' => ['items' => []], 'item_count' => 0, 'user_id' => $this->admin()->id]);
        $this->actingAs($this->admin())->delete(route('retail.held-sales.destroy', $held))->assertRedirect(route('retail.sales.create'));
        $this->assertSame(0, HeldSale::count());
    }
}
