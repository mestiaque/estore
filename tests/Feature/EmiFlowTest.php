<?php

namespace ME\Retail\Tests\Feature;

use ME\Retail\Enums\EmiFeeType;
use ME\Retail\Enums\EmiInterestType;
use ME\Retail\Models\EmiBank;
use ME\Retail\Models\EmiContract;
use ME\Retail\Models\EmiPlan;
use ME\Retail\Services\PurchaseService;
use ME\Retail\Services\SaleService;
use ME\Retail\Tests\TestCase;

class EmiFlowTest extends TestCase
{
    use CreatesCatalog;

    public function test_emi_contract_schedules_the_sales_due_amount_and_completes_when_fully_paid(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $customer  = $this->createCustomer();
        $product   = $this->createProduct(['sale_price' => 12000]);

        app(PurchaseService::class)->create([
            'supplier_id' => $supplier->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 1, 'purchase_price' => 8000]],
        ], $this->admin()->id);

        // Sale entirely on credit — the customer's due (12000) becomes the EMI principal.
        $sale = app(SaleService::class)->create([
            'customer_id' => $customer->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'sale_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 1, 'sale_price' => 12000]],
        ], $this->admin()->id);

        $this->assertSame('12000.00', $sale->due_amount);

        $bank = EmiBank::create(['name' => 'Test Bank', 'code' => 'TB', 'status' => true]);
        $plan = EmiPlan::create([
            'emi_bank_id' => $bank->id, 'name' => '3 Months', 'duration_months' => 3,
            'interest_type' => EmiInterestType::Flat, 'interest_rate' => 0,
            'fee_type' => EmiFeeType::Flat, 'fee_value' => 0,
            'min_amount' => 0, 'status' => true,
        ]);

        $response = $this->actingAs($this->admin())->post(route('retail.emi-contracts.store', $sale->id), [
            'emi_bank_id' => $bank->id, 'emi_plan_id' => $plan->id,
            'down_payment' => 0, 'start_date' => now()->toDateString(),
        ]);

        $contract = EmiContract::first();
        $response->assertRedirect(route('retail.emi-contracts.show', $contract->id));
        $this->assertSame('active', $contract->status->value);
        $this->assertSame(3, $contract->installments()->count());

        // 12000 / 3 = 4000 per installment, no interest/fee configured.
        $first = $contract->installments()->where('installment_number', 1)->first();
        $this->assertSame('4000.00', $first->amount);

        foreach ($contract->installments as $installment) {
            $this->actingAs($this->admin())->post(route('retail.emi-installments.pay', $installment->id), [
                'amount' => $installment->amount,
                'payment_method' => 'cash',
            ]);
        }

        $contract->refresh();
        $sale->refresh();
        $customer->refresh();

        $this->assertSame('completed', $contract->status->value);
        $this->assertSame('0.00', $sale->due_amount);
        $this->assertSame(0.0, $customer->due_balance);
    }

    public function test_installment_payment_cannot_exceed_its_remaining_balance(): void
    {
        $store     = $this->createStore();
        $warehouse = $this->createWarehouse($store);
        $supplier  = $this->createSupplier();
        $customer  = $this->createCustomer();
        $product   = $this->createProduct(['sale_price' => 3000]);

        app(PurchaseService::class)->create([
            'supplier_id' => $supplier->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'purchase_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 1, 'purchase_price' => 2000]],
        ], $this->admin()->id);

        $sale = app(SaleService::class)->create([
            'customer_id' => $customer->id, 'store_id' => $store->id, 'warehouse_id' => $warehouse->id,
            'sale_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'unit_id' => $product->unit_id, 'quantity' => 1, 'sale_price' => 3000]],
        ], $this->admin()->id);

        $bank = EmiBank::create(['name' => 'Test Bank', 'code' => 'TB2', 'status' => true]);
        $plan = EmiPlan::create([
            'emi_bank_id' => $bank->id, 'name' => '3 Months', 'duration_months' => 3,
            'interest_type' => EmiInterestType::Flat, 'interest_rate' => 0,
            'fee_type' => EmiFeeType::Flat, 'fee_value' => 0, 'min_amount' => 0, 'status' => true,
        ]);

        $this->actingAs($this->admin())->post(route('retail.emi-contracts.store', $sale->id), [
            'emi_bank_id' => $bank->id, 'emi_plan_id' => $plan->id, 'down_payment' => 0, 'start_date' => now()->toDateString(),
        ]);

        $installment = EmiContract::first()->installments()->first();

        $this->actingAs($this->admin())->post(route('retail.emi-installments.pay', $installment->id), [
            'amount' => (float) $installment->amount + 500,
            'payment_method' => 'cash',
        ]);

        $installment->refresh();
        $this->assertSame('pending', $installment->status->value, 'an overpayment beyond the installment amount must be rejected');
        $this->assertSame('0.00', $installment->paid_amount);
    }
}
