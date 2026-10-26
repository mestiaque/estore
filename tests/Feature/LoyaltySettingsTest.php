<?php

namespace ME\Retail\Tests\Feature;

use ME\Retail\Models\LoyaltyTransaction;
use ME\Retail\Services\PurchaseService;
use ME\Retail\Services\SaleService;
use ME\Retail\Tests\TestCase;

class LoyaltySettingsTest extends TestCase
{
    use CreatesCatalog;

    public function test_admin_can_view_and_update_loyalty_settings(): void
    {
        $response = $this->actingAs($this->admin())->get(route('retail.settings.loyalty.edit'));
        $response->assertOk();
        $response->assertSee('point_value', false);

        $update = $this->actingAs($this->admin())->put(route('retail.settings.loyalty.update'), [
            'point_value'        => 0.5,
            'max_redeem_percent' => 20,
        ]);
        $update->assertRedirect(route('retail.settings.loyalty.edit'));

        $this->assertSame(0.5, (float) \ME\Models\Setting::get('retail_loyalty_point_value'));
        $this->assertSame(20.0, (float) \ME\Models\Setting::get('retail_loyalty_max_redeem_percent'));
    }

    public function test_redemption_uses_the_master_configured_point_value(): void
    {
        \ME\Models\Setting::set('retail_loyalty_point_value', 0.5);
        \ME\Models\Setting::set('retail_loyalty_max_redeem_percent', 100);

        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $customer  = $this->createCustomer();
        $product   = $this->createProduct(['sale_price' => 100]);

        app(PurchaseService::class)->create([
            'supplier_id' => $supplier->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 5, 'purchase_price' => 50]],
        ], $this->admin()->id);

        LoyaltyTransaction::create(['customer_id' => $customer->id, 'type' => 'earn', 'points' => 100, 'balance_after' => 100]);

        // With point_value = 0.5, redeeming 50 points should discount 25 (not the 0.1-based default of 5).
        $sale = app(SaleService::class)->create([
            'customer_id' => $customer->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'sale_date' => now()->toDateString(), 'redeem_points' => 50,
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 1, 'sale_price' => 100]],
        ], $this->admin()->id);

        $this->assertSame('25.00', $sale->points_discount);
        $this->assertSame('75.00', $sale->grand_total);
    }

    public function test_customer_show_page_lists_purchase_history(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $customer  = $this->createCustomer();
        $product   = $this->createProduct(['sale_price' => 100]);

        app(PurchaseService::class)->create([
            'supplier_id' => $supplier->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 5, 'purchase_price' => 50]],
        ], $this->admin()->id);

        $sale = app(SaleService::class)->create([
            'customer_id' => $customer->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'sale_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 1, 'sale_price' => 100]],
            'payments' => [['payment_method' => 'cash', 'amount' => 100]],
        ], $this->admin()->id);

        $response = $this->actingAs($this->admin())->get(route('retail.customers.show', $customer->id));
        $response->assertOk();
        $response->assertSee('Purchase History');
        $response->assertSee($sale->invoice_number);
    }
}
