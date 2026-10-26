<?php

namespace ME\Retail\Tests\Feature;

use ME\Retail\Enums\DiscountRuleType;
use ME\Retail\Enums\DiscountValueType;
use ME\Retail\Models\DiscountRule;
use ME\Retail\Models\LoyaltyTransaction;
use ME\Retail\Models\Sale;
use ME\Retail\Services\PurchaseService;
use ME\Retail\Services\SaleService;
use ME\Retail\Tests\TestCase;

class DiscountAndLoyaltyRedemptionTest extends TestCase
{
    use CreatesCatalog;

    public function test_coupon_code_reduces_grand_total_and_is_marked_used(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $product   = $this->createProduct(['sale_price' => 100]);

        app(PurchaseService::class)->create([
            'supplier_id' => $supplier->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 5, 'purchase_price' => 50]],
        ], $this->admin()->id);

        $coupon = DiscountRule::create([
            'name' => 'Welcome10', 'type' => DiscountRuleType::Coupon, 'coupon_code' => 'WELCOME10',
            'discount_type' => DiscountValueType::Percent, 'discount_value' => 10, 'status' => true, 'used_count' => 0,
        ]);

        $sale = app(SaleService::class)->create([
            'store_id' => $store->id, 'warehouse_id' => $warehouse->id, 'sale_date' => now()->toDateString(),
            'coupon_code' => 'WELCOME10',
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 2, 'sale_price' => 100]],
            'payments' => [['payment_method' => 'cash', 'amount' => 180]],
        ], $this->admin()->id);

        // 200 subtotal - 10% coupon = 180.
        $this->assertSame('180.00', $sale->grand_total);
        $this->assertSame('20.00', $sale->auto_discount);
        $this->assertSame('WELCOME10', $sale->coupon_code);

        $coupon->refresh();
        $this->assertSame(1, $coupon->used_count);
    }

    public function test_redeeming_points_reduces_grand_total_and_deducts_customer_balance(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $customer  = $this->createCustomer();
        $product   = $this->createProduct(['sale_price' => 100, 'earn_points' => 0]);

        app(PurchaseService::class)->create([
            'supplier_id' => $supplier->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 5, 'purchase_price' => 50]],
        ], $this->admin()->id);

        // Give the customer 200 points manually via the loyalty ledger (as an Earn transaction from a prior sale would).
        LoyaltyTransaction::create([
            'customer_id' => $customer->id, 'type' => 'earn', 'points' => 200, 'balance_after' => 200,
        ]);
        $this->assertSame(200.0, $customer->points_balance);

        // retail.loyalty_point_value defaults to 0.1 -> 100 points = 10 currency.
        $sale = app(SaleService::class)->create([
            'customer_id' => $customer->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'sale_date' => now()->toDateString(), 'redeem_points' => 100,
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 1, 'sale_price' => 100]],
            'payments' => [['payment_method' => 'cash', 'amount' => 90]],
        ], $this->admin()->id);

        $this->assertSame('10.00', $sale->points_discount);
        $this->assertSame('90.00', $sale->grand_total);

        $customer->refresh();
        $this->assertSame(100.0, $customer->points_balance, '200 - 100 redeemed = 100 remaining');
    }

    public function test_cannot_redeem_more_points_than_the_customer_has(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $customer  = $this->createCustomer(); // 0 points
        $product   = $this->createProduct(['sale_price' => 100]);

        app(PurchaseService::class)->create([
            'supplier_id' => $supplier->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 5, 'purchase_price' => 50]],
        ], $this->admin()->id);

        $this->actingAs($this->admin())->post(route('retail.sales.store'), [
            'customer_id' => $customer->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'sale_date' => now()->toDateString(), 'redeem_points' => 500,
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 1, 'sale_price' => 100]],
            'payments' => [['payment_method' => 'cash', 'amount' => 100]],
        ]);

        $this->assertSame(0, Sale::count(), 'redeeming more points than the customer has must be rejected');
    }
}
