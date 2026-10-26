<?php

namespace ME\Retail\Tests\Feature;

use ME\Retail\Tests\TestCase;

class SmokeTest extends TestCase
{
    public function test_migrations_run_and_admin_user_exists(): void
    {
        $admin = $this->admin();

        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasPermission('anything.at.all'));
    }

    public function test_dashboard_renders_with_charts(): void
    {
        $response = $this->actingAs($this->admin())->get(route('retail.index'));

        $response->assertOk();
        $response->assertSee('retail-trend-chart');
        $response->assertSee('retail-payment-chart');
        $response->assertSee('retail-category-chart');
        $response->assertSee('retail-stock-chart');
        $response->assertSee('retail-level-chart');

        // One tile per sidebar menu — the super_admin bypasses every @can gate,
        // so every module's widget should render for this request.
        $response->assertSee('Catalog & Setup', false);
        $response->assertSee('Transactions');
        $response->assertSee('Finance & Loyalty', false);
        $response->assertSeeText('EMI Banks');
        $response->assertSee('Recent Stock Movements');
    }
}
