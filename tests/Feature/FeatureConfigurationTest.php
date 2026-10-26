<?php

namespace ME\Retail\Tests\Feature;

use ME\Retail\Models\Membership;
use ME\Retail\Services\BusinessTypeService;
use ME\Retail\Services\PurchaseService;
use ME\Retail\Services\SaleService;
use ME\Retail\Support\RetailModules;
use ME\Retail\Tests\TestCase;

class FeatureConfigurationTest extends TestCase
{
    use CreatesCatalog;

    private function stockedProduct($warehouse, array $overrides = [])
    {
        $product = $this->createProduct(array_merge(['sale_price' => 100, 'earn_points' => 1], $overrides));

        app(PurchaseService::class)->create([
            'supplier_id' => $this->createSupplier()->id, 'store_id' => $warehouse->store_id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 10, 'purchase_price' => 50]],
        ], $this->admin()->id);

        return $product;
    }

    public function test_pos_shows_an_autofocused_scan_box_only_while_barcode_is_on(): void
    {
        $this->actingAs($this->admin())->get(route('retail.sales.create'))
            ->assertOk()
            ->assertSee('id="barcode-scan" class="form-control pos-search-input" autofocus', false);

        RetailModules::set('barcode', false);

        $this->actingAs($this->admin())->get(route('retail.sales.create'))
            ->assertOk()
            ->assertDontSee('id="barcode-scan"', false)
            ->assertSee('id="product-search"', false);
        $this->actingAs($this->admin())->get(route('retail.purchases.create'))
            ->assertOk()
            ->assertDontSee('id="barcode-scan"', false);
    }

    public function test_switched_off_feature_routes_are_blocked(): void
    {
        RetailModules::set('barcode', false);
        RetailModules::set('emi', false);
        RetailModules::set('membership', false);

        $this->actingAs($this->admin())->get(route('retail.barcode-labels.form'))->assertForbidden();
        $this->actingAs($this->admin())->getJson(route('retail.barcode.lookup', ['code' => 'X', 'context' => 'sale']))->assertForbidden();
        $this->actingAs($this->admin())->get(route('retail.emi-banks.index'))->assertForbidden();
        $this->actingAs($this->admin())->get(route('retail.memberships.index'))->assertForbidden();

        // Everything not tied to a feature keeps working.
        $this->actingAs($this->admin())->get(route('retail.products.index'))->assertOk();
        $this->actingAs($this->admin())->get(route('retail.settings.modules.edit'))->assertOk();
    }

    public function test_a_feature_is_inactive_while_a_feature_it_requires_is_off(): void
    {
        $this->assertTrue(RetailModules::enabled('warranty'));

        RetailModules::set('serial', false);

        $this->assertTrue(RetailModules::switchedOn('warranty'));
        $this->assertFalse(RetailModules::enabled('warranty'), 'warranty needs serial tracking');
    }

    public function test_sidebar_drops_switched_off_features_and_empty_groups(): void
    {
        RetailModules::set('stock_transfer', false);
        RetailModules::set('cash_register', false);
        RetailModules::set('expense', false);

        $groups = RetailModules::filterSidebar(require __DIR__ . '/../../src/Config/sidebar.php');
        $routes = collect($groups)->flatMap(fn ($g) => collect($g['children'] ?? [])->pluck('route'))->all();

        $this->assertNotContains('retail.stock-transfers.index', $routes);
        $this->assertNotContains('Cash & Expense', collect($groups)->pluck('title')->all(), 'a group with no items left disappears');
        $this->assertContains('retail.stock-ledgers.index', $routes);
    }

    public function test_hidden_fields_disappear_from_pos_and_product_form(): void
    {
        RetailModules::set('tax', false);
        RetailModules::set('discount', false);
        RetailModules::set('loyalty', false);
        RetailModules::set('hold_sale', false);
        RetailModules::set('serial', false);

        $this->actingAs($this->admin())->get(route('retail.sales.create'))
            ->assertOk()
            ->assertDontSee('name="tax" class', false)
            ->assertDontSee('name="coupon_code" class', false)
            ->assertDontSee('name="redeem_points" id', false)
            ->assertDontSee('Hold Sale');

        $this->actingAs($this->admin())->get(route('retail.products.create'))
            ->assertOk()
            ->assertDontSee('value="serial"', false)
            ->assertDontSee('name="earn_points" class', false);
    }

    public function test_switched_off_membership_and_loyalty_no_longer_affect_a_sale(): void
    {
        $warehouse = $this->createWarehouse($this->createStore());
        $product   = $this->stockedProduct($warehouse);
        $membership = Membership::create([
            'name' => 'Gold', 'code' => 'GOLD', 'fee' => 0, 'duration_days' => 365,
            'discount_percent' => 10, 'point_multiplier' => 1, 'status' => true,
        ]);
        $customer = $this->createCustomer();
        $customer->update(['membership_id' => $membership->id, 'membership_expires_at' => now()->addYear()]);

        $sell = fn () => app(SaleService::class)->create([
            'customer_id' => $customer->id, 'store_id' => $warehouse->store_id, 'warehouse_id' => $warehouse->id,
            'sale_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 1, 'sale_price' => 100]],
        ], $this->admin()->id);

        $withFeatures = $sell();
        $this->assertSame('10.00', $withFeatures->auto_discount);
        $this->assertSame('1.00', $withFeatures->points_earned);

        RetailModules::set('membership', false);
        RetailModules::set('loyalty', false);

        $without = $sell();
        $this->assertSame('0.00', $without->auto_discount);
        $this->assertSame('0.00', $without->points_earned);
    }

    public function test_configuration_page_saves_every_switch(): void
    {
        $this->actingAs($this->admin())->put(route('retail.settings.modules.update'), [
            'features' => ['barcode' => '1', 'emi' => '1'],
        ])->assertRedirect(route('retail.settings.modules.edit'));

        $this->assertTrue(RetailModules::enabled('barcode'));
        $this->assertTrue(RetailModules::enabled('emi'));
        $this->assertFalse(RetailModules::enabled('membership'));
        $this->assertFalse(RetailModules::enabled('hold_sale'));
    }

    public function test_business_type_preset_switches_features(): void
    {
        app(BusinessTypeService::class)->apply('pharmacy');

        $this->assertTrue(RetailModules::enabled('batch'));
        $this->assertFalse(RetailModules::enabled('serial'));
        $this->assertFalse(RetailModules::enabled('emi'));
        $this->assertTrue(RetailModules::enabled('barcode'));

        app(BusinessTypeService::class)->apply('mobile');

        $this->assertTrue(RetailModules::enabled('serial'));
        $this->assertTrue(RetailModules::enabled('warranty'));
        $this->assertFalse(RetailModules::enabled('batch'));
    }
}
