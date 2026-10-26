<?php

namespace ME\Retail\Tests\Feature;

use ME\Models\Setting;
use ME\Retail\Tests\TestCase;

class ModuleSettingsTest extends TestCase
{
    use CreatesCatalog;

    public function test_admin_can_view_and_update_module_settings(): void
    {
        $response = $this->actingAs($this->admin())->get(route('retail.settings.modules.edit'));
        $response->assertOk();
        foreach (array_keys(config('retail_features.features')) as $feature) {
            $response->assertSee('name="features[' . $feature . ']"', false);
        }

        // Submitting with only "discount" checked turns every other feature off.
        $update = $this->actingAs($this->admin())->put(route('retail.settings.modules.update'), [
            'features' => ['discount' => '1'],
        ]);
        $update->assertRedirect(route('retail.settings.modules.edit'));

        $this->assertTrue((bool) Setting::get('retail_enable_discount_module'));
        $this->assertFalse((bool) Setting::get('retail_enable_loyalty_module'));
        $this->assertFalse((bool) Setting::get('retail_enable_emi_module'));
    }

    public function test_sidebar_hides_disabled_modules(): void
    {
        Setting::set('retail_enable_emi_module', false);
        Setting::set('retail_enable_discount_module', false);
        Setting::set('retail_enable_loyalty_module', false);

        // RetailModules::filterSidebar() is what RetailServiceProvider::boot() calls on every
        // fresh application boot (i.e. every real request) — tested directly here rather than
        // by re-invoking boot() mid-test, since a second boot() would just array_merge a second,
        // filtered copy on top of the first unfiltered one already sitting in config('sidebar').
        $groups = \ME\Retail\Support\RetailModules::filterSidebar(require __DIR__ . '/../../src/Config/sidebar.php');

        $titles = collect($groups)->pluck('title')->all();
        $this->assertNotContains('EMI', $titles);

        $allRoutes = collect($groups)->flatMap(fn ($g) => collect($g['children'] ?? [])->pluck('route'))->all();
        $this->assertNotContains('retail.discount-rules.index', $allRoutes);
        $this->assertNotContains('retail.customer-levels.index', $allRoutes);
        $this->assertNotContains('retail.loyalty.adjust.create', $allRoutes);
        $this->assertNotContains('retail.settings.loyalty.edit', $allRoutes);
        $this->assertNotContains('retail.reports.loyalty', $allRoutes);
        $this->assertNotContains('retail.reports.emi', $allRoutes);

        // The dashboard reads the same flags directly at render time, so it stays correct
        // on every request regardless of when the sidebar itself was last rebuilt. (The
        // sidebar's own "EMI Banks" text is skipped here — it was built once from
        // RetailServiceProvider::boot() before these settings existed, in setUp(); a real
        // request boots fresh every time, so this staleness never happens in production.)
        $response = $this->actingAs($this->admin())->get(route('retail.index'));
        $response->assertOk();
        $response->assertDontSee('Active Discount Rules');
        $response->assertDontSee('Points Earned (month)');
        $response->assertDontSee('Active EMI Contracts');
    }

    public function test_sale_and_purchase_create_auto_select_when_only_one_store_and_warehouse(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);

        $response = $this->actingAs($this->admin())->get(route('retail.sales.create'));
        $response->assertOk();
        $response->assertSee('type="hidden" name="store_id" value="' . $store->id . '"', false);
        $response->assertSee('type="hidden" name="warehouse_id" value="' . $warehouse->id . '"', false);
        $response->assertDontSee('Select Store');
        $response->assertDontSee('Select Warehouse');
    }
}
