<?php

namespace ME\Retail\Tests\Feature;

use ME\Models\Setting;
use ME\Retail\Models\Unit;
use ME\Retail\Services\BusinessTypeService;
use ME\Retail\Support\RetailModules;
use ME\Retail\Tests\TestCase;

class BusinessTypeTest extends TestCase
{
    use CreatesCatalog;

    public function test_settings_page_lists_every_business_type(): void
    {
        $response = $this->actingAs($this->admin())->get(route('retail.settings.business-type.edit'));

        $response->assertOk();
        foreach (array_keys(config('retail_business_types.types')) as $type) {
            $response->assertSee('value="' . $type . '"', false);
        }
    }

    public function test_applying_pharmacy_sets_modules_tracking_defaults_and_units(): void
    {
        $this->actingAs($this->admin())
            ->put(route('retail.settings.business-type.update'), ['business_type' => 'pharmacy'])
            ->assertRedirect(route('retail.settings.business-type.edit'))
            ->assertSessionHas('success');

        $this->assertSame('pharmacy', Setting::get('retail_business_type'));
        $this->assertFalse(RetailModules::emiEnabled());
        $this->assertTrue(RetailModules::discountEnabled());

        $this->assertSame(['track_type' => 'batch', 'has_expiry' => true], app(BusinessTypeService::class)->productDefaults());

        $millilitre = Unit::where('short_name', 'ml')->first();
        $this->assertNotNull($millilitre);
        $this->assertSame(Unit::where('short_name', 'L')->value('id'), $millilitre->base_unit_id);
        $this->assertSame('0.0010', $millilitre->conversion_factor);
    }

    public function test_new_product_form_preselects_the_presets_tracking(): void
    {
        app(BusinessTypeService::class)->apply('mobile');

        $this->actingAs($this->admin())->get(route('retail.products.create'))
            ->assertOk()
            ->assertSee('<option value="serial" selected>', false);
    }

    public function test_reapplying_or_switching_type_does_not_duplicate_existing_units(): void
    {
        // A shop that already created its own "Kilogram" unit before choosing a type.
        $this->createUnit()->update(['name' => 'Kilogram', 'short_name' => 'KG']);

        $service = app(BusinessTypeService::class);
        $created = $service->apply('grocery');
        $this->assertSame(5, $created, 'piece, dozen, gram, litre and ml are new — kg already existed');

        $this->assertSame(0, $service->apply('grocery'));
        $this->assertSame(1, Unit::whereRaw('lower(short_name) = ?', ['kg'])->count());

        $gram = Unit::where('short_name', 'g')->first();
        $this->assertSame(Unit::where('short_name', 'KG')->value('id'), $gram->base_unit_id, 'derived units attach to the existing base unit');

        // Switching type changes defaults but leaves existing units alone.
        $service->apply('electronics');
        $this->assertTrue(RetailModules::emiEnabled());
        $this->assertNotNull(Unit::where('short_name', 'g')->first());
    }

    public function test_unknown_business_type_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->put(route('retail.settings.business-type.update'), ['business_type' => 'spaceship'])
            ->assertSessionHasErrors('business_type');

        $this->assertNull(Setting::get('retail_business_type'));
    }

    public function test_dashboard_prompts_for_a_business_type_until_one_is_chosen(): void
    {
        $this->actingAs($this->admin())->get(route('retail.index'))
            ->assertOk()
            ->assertSee(__('Choose Business Type'));

        app(BusinessTypeService::class)->apply('general');

        $this->actingAs($this->admin())->get(route('retail.index'))
            ->assertOk()
            ->assertDontSee(__('Choose Business Type'));
    }
}
