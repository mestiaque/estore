<?php

namespace ME\Retail\Tests\Feature;

use ME\Retail\Enums\StockMovementType;
use ME\Retail\Models\Sale;
use ME\Retail\Services\BarcodeService;
use ME\Retail\Services\BusinessTypeService;
use ME\Retail\Services\PurchaseService;
use ME\Retail\Services\ReportService;
use ME\Retail\Services\StockService;
use ME\Retail\Support\RetailModules;
use ME\Retail\Support\RetailSettings;
use ME\Retail\Tests\TestCase;

class RetailSettingsUiTest extends TestCase
{
    use CreatesCatalog;
    use SubmitsRetailSettings;

    private function saveSettings(array $overrides)
    {
        return $this->actingAs($this->admin())->put(route('retail.settings.general.update'), [
            'settings' => array_merge($this->currentSettingsPayload(), $overrides),
        ]);
    }

    public function test_settings_page_shows_every_visible_setting_with_config_defaults(): void
    {
        $response = $this->actingAs($this->admin())->get(route('retail.settings.general.edit'))->assertOk();

        foreach (array_keys(RetailSettings::visible()) as $key) {
            $response->assertSee('settings[' . RetailSettings::fieldName($key) . ']', false);
        }

        $this->assertSame('STORE', RetailSettings::get('barcode_prefix'));
        $this->assertSame(10, RetailSettings::get('low_stock_threshold'));
        $this->assertFalse(RetailSettings::get('allow_negative_stock'));
    }

    public function test_saved_values_are_used_by_the_system(): void
    {
        $this->saveSettings([
            'barcode_prefix'        => 'SHOP',
            'barcode_number_length' => 4,
            'low_stock_threshold'   => 3,
            'allow_negative_stock'  => '1',
            'label_sizes'           => [['width' => 38, 'height' => 25], ['width' => 50.5, 'height' => 30]],
        ])->assertRedirect(route('retail.settings.general.edit'))->assertSessionHasNoErrors();

        // Generated barcode format.
        $product = $this->createProduct();
        app(BarcodeService::class)->assign($product);
        $this->assertSame('SHOP-' . str_pad((string) $product->id, 4, '0', STR_PAD_LEFT), $product->refresh()->barcode);

        // Negative stock now allowed.
        $warehouse = $this->createWarehouse($this->createStore());
        app(StockService::class)->move($product, $warehouse, -2, StockMovementType::Adjustment);
        $this->assertSame(-2.0, app(StockService::class)->currentQuantity($product, $warehouse));

        // Label sizes are offered on the label print form.
        $this->assertSame(['38x25', '50.5x30'], array_keys(RetailSettings::get('label_sizes')));
        $this->actingAs($this->admin())->get(route('retail.barcode-labels.form'))->assertOk()->assertSee('50.5x30 mm');

        // Low stock threshold drives the report: 2 on hand is "low" at a threshold of 3.
        $lowProduct = $this->createProduct();
        app(StockService::class)->move($lowProduct, $warehouse, 2, StockMovementType::Adjustment);
        $this->assertTrue(app(ReportService::class)->lowStockProducts()->contains('id', $lowProduct->id));
    }

    public function test_invalid_values_are_rejected(): void
    {
        $this->saveSettings([
            'barcode_prefix'          => 'BAD PREFIX!',
            'low_stock_threshold'     => -5,
            'weighed_barcode__prefixes' => 'ab,12',
        ])->assertSessionHasErrors(['settings.barcode_prefix', 'settings.low_stock_threshold']);

        $this->assertSame('STORE', RetailSettings::get('barcode_prefix'));
    }

    public function test_settings_of_a_switched_off_feature_are_hidden_and_kept(): void
    {
        RetailModules::set('loyalty', false);

        $this->actingAs($this->admin())->get(route('retail.settings.general.edit'))
            ->assertOk()
            ->assertDontSee('settings[loyalty_point_value]', false);

        // Saving the form without the hidden fields leaves them untouched.
        $this->saveSettings(['low_stock_threshold' => 7])->assertSessionHasNoErrors();
        $this->assertSame(0.1, RetailSettings::get('loyalty_point_value'));
        $this->assertSame(7, RetailSettings::get('low_stock_threshold'));
    }

    public function test_invoice_formats_follow_the_settings(): void
    {
        $this->saveSettings(['invoice_types' => ['thermal80'], 'default_invoice_type' => 'a4'])->assertSessionHasNoErrors();

        $this->assertSame(['thermal80'], RetailSettings::get('invoice_types'));
        $this->assertSame('thermal80', RetailSettings::get('default_invoice_type'), 'the default falls back to an offered format');

        $warehouse = $this->createWarehouse($this->createStore());
        $product = $this->createProduct();
        app(PurchaseService::class)->create([
            'supplier_id' => $this->createSupplier()->id, 'store_id' => $warehouse->store_id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 5, 'purchase_price' => 10]],
        ], $this->admin()->id);
        $this->actingAs($this->admin())->post(route('retail.sales.store'), [
            'store_id' => $warehouse->store_id, 'warehouse_id' => $warehouse->id, 'sale_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 1, 'sale_price' => 80]],
            'payments' => [['payment_method' => 'cash', 'amount' => 80]],
        ]);
        $sale = Sale::first();

        $this->actingAs($this->admin())->get(route('retail.sales.invoice', [$sale, 'a4']))->assertNotFound();
        $this->actingAs($this->admin())->get(route('retail.sales.invoice', [$sale, 'thermal80']))->assertOk();
        $this->actingAs($this->admin())->get(route('retail.sales.show', $sale))->assertOk()->assertDontSee(__('A4 Invoice'));
    }

    public function test_business_type_presets_can_be_added_edited_deleted_and_reset(): void
    {
        $service = app(BusinessTypeService::class);

        // Add a new preset from the UI.
        $this->actingAs($this->admin())->get(route('retail.settings.business-types.create'))->assertOk();
        $this->actingAs($this->admin())->post(route('retail.settings.business-types.store'), [
            'label' => 'Book Shop', 'description' => 'Books and stationery', 'track_type' => 'normal',
            'disabled' => ['emi', 'batch', 'serial'], 'units' => ['piece', 'dozen'],
        ])->assertRedirect(route('retail.settings.business-type.edit'));

        $this->assertArrayHasKey('book_shop', $service->types());
        $this->assertSame(['emi', 'batch', 'serial'], $service->types()['book_shop']['disabled']);

        // Edit an existing preset: grocery now also switches off membership.
        $this->actingAs($this->admin())->get(route('retail.settings.business-types.edit', 'grocery'))->assertOk()->assertSee('Grocery / Super Shop');
        $this->actingAs($this->admin())->put(route('retail.settings.business-types.update', 'grocery'), [
            'label' => 'Super Shop', 'track_type' => 'serial', 'disabled' => ['serial', 'membership'], 'units' => ['kg'],
        ])->assertRedirect(route('retail.settings.business-type.edit'));

        $grocery = $service->types()['grocery'];
        $this->assertSame('Super Shop', $grocery['label']);
        $this->assertSame('normal', $grocery['track_type'], 'a tracking type the preset switches off falls back to normal');

        $service->apply('grocery');
        $this->assertFalse(RetailModules::enabled('membership'));

        // The preset in use can't be deleted; another one can.
        $this->actingAs($this->admin())->delete(route('retail.settings.business-types.destroy', 'grocery'))->assertSessionHas('error');
        $this->actingAs($this->admin())->delete(route('retail.settings.business-types.destroy', 'book_shop'))->assertSessionHas('success');
        $this->assertArrayNotHasKey('book_shop', $service->types());

        // Reset restores the shipped presets.
        $this->actingAs($this->admin())->post(route('retail.settings.business-types.reset'))->assertSessionHas('success');
        $this->assertSame('Grocery / Super Shop', $service->types()['grocery']['label']);
        $this->assertFalse($service->isCustomised());
    }
}
