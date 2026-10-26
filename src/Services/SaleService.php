<?php

namespace ME\Retail\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use ME\Retail\Enums\CashRegisterTransactionType;
use ME\Retail\Enums\CustomerTransactionType;
use ME\Retail\Enums\PaymentStatus;
use ME\Retail\Enums\StockMovementType;
use ME\Retail\Models\Batch;
use ME\Retail\Models\CashRegisterSession;
use ME\Retail\Models\Customer;
use ME\Retail\Models\Product;
use ME\Retail\Models\Sale;
use ME\Retail\Models\SaleItem;
use ME\Retail\Models\SalePayment;
use ME\Retail\Models\Unit;
use ME\Retail\Models\Warehouse;
use ME\Retail\Support\RetailModules;
use ME\Retail\Support\RetailSettings;

/**
 * Orchestrates the POS/Sale flow: Customer -> Product -> Pricing -> Payment ->
 * Sale -> Stock Ledger, all inside a single atomic transaction. Mixed
 * payment is native — a sale accepts any number of payment rows.
 */
class SaleService
{
    public function __construct(
        protected StockService $stockService,
        protected CustomerLedgerService $customerLedgerService,
        protected BatchService $batchService,
        protected SerialService $serialService,
        protected LoyaltyService $loyaltyService,
        protected PricingService $pricingService,
        protected CashRegisterService $cashRegisterService
    ) {
    }

    /**
     * @param array{
     *   customer_id?:?int, store_id:int, warehouse_id:int, sale_date:string, discount?:float, tax?:float,
     *   redeem_points?:float, coupon_code?:string, notes?:string,
     *   items: array<int, array{product_id:int, unit_id:int, quantity:float, sale_price:float, discount?:float, tax?:float, batch_id?:int, serial_ids?:int[]}>,
     *   payments?: array<int, array{payment_method:string, amount:float, reference?:string}>
     * } $data
     */
    public function create(array $data, ?int $userId): Sale
    {
        return DB::transaction(function () use ($data, $userId) {
            $warehouse = Warehouse::findOrFail($data['warehouse_id']);
            $customer  = !empty($data['customer_id']) ? Customer::findOrFail($data['customer_id']) : null;

            $itemRows = [];
            $subtotal = 0.0;

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $unit    = Unit::findOrFail($item['unit_id']);

                if ($unit->id !== $product->unit_id && $unit->base_unit_id !== $product->unit_id) {
                    throw new InvalidArgumentException(
                        "Unit \"{$unit->name}\" is not a valid sale unit for product \"{$product->name}\"."
                    );
                }

                $quantity     = (float) $item['quantity'];
                $factor       = (float) $unit->conversion_factor ?: 1.0;
                $quantityBase = $quantity * $factor;

                $batch = null;
                if ($product->track_type === 'batch') {
                    if (empty($item['batch_id'])) {
                        throw new InvalidArgumentException("Select a batch for product \"{$product->name}\".");
                    }

                    $batch = Batch::findOrFail($item['batch_id']);
                    if ($batch->product_id !== $product->id || $batch->warehouse_id !== $warehouse->id) {
                        throw new InvalidArgumentException("Batch does not belong to product \"{$product->name}\" in the selected warehouse.");
                    }
                }

                $serialIds = [];
                if ($product->track_type === 'serial') {
                    $serialIds = array_values(array_filter($item['serial_ids'] ?? []));

                    if (abs($quantityBase - round($quantityBase)) > 0.0001 || count($serialIds) !== (int) round($quantityBase)) {
                        throw new InvalidArgumentException(
                            "Product \"{$product->name}\" is serial-tracked: select exactly {$quantityBase} serial(s)."
                        );
                    }
                }

                $lineBase       = $quantity * (float) $item['sale_price'];
                $manualDiscount = (float) ($item['discount'] ?? 0);
                $auto           = $this->pricingService->itemDiscount($product, $quantity, max($lineBase - $manualDiscount, 0));
                $tax            = (float) ($item['tax'] ?? 0);
                $total          = $lineBase - $manualDiscount - $auto['amount'] + $tax;
                $subtotal += $total;

                // Cost snapshot at time of sale, in the same per-sale-unit basis as sale_price — a
                // batch-tracked item uses its specific lot's cost, never today's product cost (plan §46).
                $costPerBase = $batch ? (float) $batch->purchase_price : (float) $product->purchase_price;
                $costPrice   = $costPerBase * $factor;

                $itemRows[] = [
                    'product'         => $product,
                    'unit'            => $unit,
                    'quantity'        => $quantity,
                    'quantity_base'   => $quantityBase,
                    'sale_price'      => $item['sale_price'],
                    'cost_price'      => $costPrice,
                    'discount'        => $manualDiscount,
                    'auto_discount'   => $auto['amount'],
                    'discount_rule'   => $auto['rule'],
                    'tax'             => $tax,
                    'total'           => $total,
                    'batch'           => $batch,
                    'serial_ids'      => $serialIds,
                ];
            }

            // Points are only earned by identified customers, never on a walk-in sale, and only while loyalty is on.
            $pointMultiplier = $customer && RetailModules::loyaltyEnabled() ? $this->loyaltyService->multiplierFor($customer) : 0.0;
            foreach ($itemRows as &$row) {
                $row['points_earned'] = (float) $row['product']->earn_points * $row['quantity_base'] * $pointMultiplier;
            }
            unset($row);
            $pointsEarned = array_sum(array_column($itemRows, 'points_earned'));

            $invoiceDiscount = (float) ($data['discount'] ?? 0);
            $invoiceTax      = (float) ($data['tax'] ?? 0);
            $couponCode      = $data['coupon_code'] ?? null;

            $auto = $this->pricingService->invoiceAutoDiscount($customer, max($subtotal - $invoiceDiscount, 0), $couponCode ?: null);
            $autoDiscount = $auto['amount'];
            $couponRule   = $auto['coupon_rule'];

            $preRedeemTotal = $subtotal - $invoiceDiscount - $autoDiscount + $invoiceTax;

            $redeemPoints   = (float) ($data['redeem_points'] ?? 0);
            $pointsDiscount = 0.0;

            if ($redeemPoints > 0) {
                if (!RetailModules::loyaltyEnabled()) {
                    throw new InvalidArgumentException('Loyalty points are turned off in Retail settings.');
                }

                if (!$customer) {
                    throw new InvalidArgumentException('Points can only be redeemed by a registered customer.');
                }

                $pointsDiscount = $redeemPoints * (float) RetailSettings::get('loyalty_point_value');
                $maxRedeemable  = $preRedeemTotal * ((float) RetailSettings::get('loyalty_max_redeem_percent') / 100);

                if ($pointsDiscount > $maxRedeemable + 0.0001) {
                    throw new InvalidArgumentException("Points redemption cannot exceed {$maxRedeemable} on this invoice.");
                }
            }

            $grandTotal = max($preRedeemTotal - $pointsDiscount, 0);

            $payments   = $data['payments'] ?? [];
            $paidAmount = array_sum(array_map(fn ($p) => (float) $p['amount'], $payments));

            if ($paidAmount > $grandTotal + 0.0001) {
                throw new InvalidArgumentException('Total payment cannot exceed the sale grand total.');
            }

            if (!$customer && $paidAmount < $grandTotal - 0.0001) {
                throw new InvalidArgumentException('A walk-in sale (no customer selected) must be paid in full.');
            }

            $dueAmount = max($grandTotal - $paidAmount, 0);

            $cashSession = !empty($data['cash_register_session_id'])
                ? CashRegisterSession::findOrFail($data['cash_register_session_id'])
                : null;

            $sale = Sale::create([
                'customer_id'     => $customer?->id,
                'store_id'        => $data['store_id'],
                'warehouse_id'    => $warehouse->id,
                'invoice_number'  => $this->generateInvoiceNumber(),
                'sale_date'       => $data['sale_date'],
                'subtotal'        => $subtotal,
                'discount'        => $invoiceDiscount,
                'auto_discount'   => $autoDiscount,
                'coupon_code'     => $couponRule ? $couponCode : null,
                'discount_rule_id' => $couponRule?->id,
                'tax'             => $invoiceTax,
                'grand_total'     => $grandTotal,
                'paid_amount'     => $paidAmount,
                'due_amount'      => $dueAmount,
                'payment_status'  => PaymentStatus::fromAmounts($grandTotal, $paidAmount),
                'points_earned'   => $pointsEarned,
                'points_redeemed' => $redeemPoints,
                'points_discount' => $pointsDiscount,
                'notes'           => $data['notes'] ?? null,
                'user_id'         => $userId,
            ]);

            if ($couponRule) {
                $this->pricingService->redeemCoupon($couponRule);
            }

            if ($redeemPoints > 0 && $customer) {
                $this->loyaltyService->redeem($customer, $redeemPoints, Sale::class, $sale->id, $userId, "Redeemed on sale {$sale->invoice_number}");
            }

            foreach ($itemRows as $row) {
                // Batch/serial deduction happens before the aggregate ledger move so an
                // insufficient-quantity failure at the fine-grained level rolls back cleanly.
                if ($row['batch']) {
                    $this->batchService->decrement($row['batch'], $row['quantity_base']);
                }

                $saleItem = SaleItem::create([
                    'sale_id'          => $sale->id,
                    'product_id'       => $row['product']->id,
                    'unit_id'          => $row['unit']->id,
                    'batch_id'         => $row['batch']?->id,
                    'quantity'         => $row['quantity'],
                    'quantity_base'    => $row['quantity_base'],
                    'sale_price'       => $row['sale_price'],
                    'cost_price'       => $row['cost_price'],
                    'discount'         => $row['discount'],
                    'auto_discount'    => $row['auto_discount'],
                    'discount_rule_id' => $row['discount_rule']?->id,
                    'tax'              => $row['tax'],
                    'total'            => $row['total'],
                    'points_earned'    => $row['points_earned'],
                ]);

                if (!empty($row['serial_ids'])) {
                    $this->serialService->reserveForSale($row['product'], $row['serial_ids'], $saleItem->id);
                }

                $this->stockService->move(
                    product: $row['product'],
                    warehouse: $warehouse,
                    quantity: -$row['quantity_base'],
                    type: StockMovementType::Sale,
                    referenceType: Sale::class,
                    referenceId: $sale->id,
                    userId: $userId,
                    notes: "Sale {$sale->invoice_number}"
                );
            }

            foreach ($payments as $payment) {
                $isCash = $payment['payment_method'] === 'cash' && $cashSession;

                SalePayment::create([
                    'sale_id'                   => $sale->id,
                    'customer_id'                => $customer?->id,
                    'amount'                     => $payment['amount'],
                    'payment_method'             => $payment['payment_method'],
                    'cash_register_session_id'   => $isCash ? $cashSession->id : null,
                    'reference'                  => $payment['reference'] ?? null,
                    'user_id'                    => $userId,
                    'paid_at'                    => now(),
                ]);

                if ($isCash) {
                    $this->cashRegisterService->recordMovement(
                        session: $cashSession,
                        type: CashRegisterTransactionType::Sale,
                        amount: (float) $payment['amount'],
                        referenceType: Sale::class,
                        referenceId: $sale->id,
                        userId: $userId,
                        notes: "Sale {$sale->invoice_number}"
                    );
                }
            }

            if ($customer) {
                $this->customerLedgerService->record(
                    customer: $customer,
                    type: CustomerTransactionType::Sale,
                    amount: $grandTotal,
                    referenceType: Sale::class,
                    referenceId: $sale->id,
                    userId: $userId,
                    notes: "Sale {$sale->invoice_number}"
                );

                if ($paidAmount > 0) {
                    $this->customerLedgerService->record(
                        customer: $customer,
                        type: CustomerTransactionType::Payment,
                        amount: -$paidAmount,
                        referenceType: Sale::class,
                        referenceId: $sale->id,
                        userId: $userId,
                        notes: "Payment for sale {$sale->invoice_number}"
                    );
                }

                if ($pointsEarned > 0) {
                    $this->loyaltyService->earn($customer, $pointsEarned, Sale::class, $sale->id, $userId, "Earned on sale {$sale->invoice_number}");
                }
            }

            return $sale;
        });
    }

    private function generateInvoiceNumber(): string
    {
        $next = (int) (Sale::query()->lockForUpdate()->max('id')) + 1;

        return 'POS-' . now()->format('Y') . '-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
