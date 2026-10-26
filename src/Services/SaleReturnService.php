<?php

namespace ME\Retail\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use ME\Retail\Enums\CashRegisterTransactionType;
use ME\Retail\Enums\CustomerTransactionType;
use ME\Retail\Enums\SerialStatus;
use ME\Retail\Enums\StockMovementType;
use ME\Retail\Models\CashRegisterSession;
use ME\Retail\Models\Sale;
use ME\Retail\Models\SaleItem;
use ME\Retail\Models\SaleReturn;
use ME\Retail\Models\SaleReturnItem;
use ME\Retail\Models\Serial;

/**
 * Sale Return flow: Original Sale -> Validate Returnable Quantity -> Stock
 * Increase -> Refund/Credit, all inside a single atomic transaction. Never
 * allows returning more than was sold minus what was already returned.
 */
class SaleReturnService
{
    public function __construct(
        protected StockService $stockService,
        protected CustomerLedgerService $customerLedgerService,
        protected BatchService $batchService,
        protected SerialService $serialService,
        protected LoyaltyService $loyaltyService,
        protected CashRegisterService $cashRegisterService
    ) {
    }

    /**
     * @param array<int, array{sale_item_id:int, quantity:float, serial_ids?:int[]}> $items
     *        Quantity is expressed in the original sale item's unit; for serial-tracked
     *        items it is ignored in favor of the count of serial_ids supplied.
     */
    public function create(Sale $sale, array $items, ?string $notes, ?int $userId, ?int $cashRegisterSessionId = null): SaleReturn
    {
        return DB::transaction(function () use ($sale, $items, $notes, $userId, $cashRegisterSessionId) {
            $saleReturn = SaleReturn::create([
                'sale_id'      => $sale->id,
                'customer_id'  => $sale->customer_id,
                'warehouse_id' => $sale->warehouse_id,
                'return_date'  => now()->toDateString(),
                'total_refund' => 0,
                'notes'        => $notes,
                'user_id'      => $userId,
            ]);

            $totalRefund       = 0.0;
            $totalPointsReverse = 0.0;

            foreach ($items as $item) {
                $saleItem = SaleItem::where('sale_id', $sale->id)->findOrFail($item['sale_item_id']);
                $isSerialTracked = $saleItem->product->track_type === 'serial';

                $serialIds = $isSerialTracked ? array_values(array_filter($item['serial_ids'] ?? [])) : [];
                $quantity  = $isSerialTracked ? (float) count($serialIds) : (float) ($item['quantity'] ?? 0);

                if ($quantity <= 0) {
                    continue;
                }

                $ratio        = (float) $saleItem->quantity > 0 ? (float) $saleItem->quantity_base / (float) $saleItem->quantity : 0;
                $quantityBase = $quantity * $ratio;
                $remaining    = $saleItem->remainingReturnableBaseQuantity();

                if ($quantityBase > $remaining + 0.0001) {
                    throw new InvalidArgumentException(
                        "Cannot return {$quantity} of \"{$saleItem->product->name}\" — only " .
                        ($ratio > 0 ? round($remaining / $ratio, 4) : 0) . ' remains returnable.'
                    );
                }

                $unitRefundRate = (float) $saleItem->quantity > 0 ? (float) $saleItem->total / (float) $saleItem->quantity : 0;
                $pointsPerBaseUnit = (float) $saleItem->quantity_base > 0 ? (float) $saleItem->points_earned / (float) $saleItem->quantity_base : 0;
                $totalPointsReverse += $pointsPerBaseUnit * $quantityBase;

                if ($isSerialTracked) {
                    // One return item per serial, so each unit's lifecycle (Sold -> Available) is tracked individually.
                    foreach ($serialIds as $serialId) {
                        $serial = Serial::query()->whereKey($serialId)->firstOrFail();

                        if ($serial->product_id !== $saleItem->product_id || $serial->sale_item_id !== $saleItem->id) {
                            throw new InvalidArgumentException("Serial \"{$serial->serial_number}\" was not sold on this sale item.");
                        }
                        if ($serial->status !== SerialStatus::Sold) {
                            throw new InvalidArgumentException("Serial \"{$serial->serial_number}\" cannot be returned from status {$serial->status->label()}.");
                        }

                        $refundAmount = $unitRefundRate;
                        $totalRefund += $refundAmount;

                        SaleReturnItem::create([
                            'sale_return_id' => $saleReturn->id,
                            'sale_item_id'   => $saleItem->id,
                            'product_id'     => $saleItem->product_id,
                            'serial_id'      => $serial->id,
                            'quantity'       => 1,
                            'quantity_base'  => $ratio,
                            'refund_amount'  => $refundAmount,
                        ]);

                        $this->serialService->markReturned($serial);
                    }
                } else {
                    $refundAmount = $quantity * $unitRefundRate;
                    $totalRefund += $refundAmount;

                    SaleReturnItem::create([
                        'sale_return_id' => $saleReturn->id,
                        'sale_item_id'   => $saleItem->id,
                        'product_id'     => $saleItem->product_id,
                        'quantity'       => $quantity,
                        'quantity_base'  => $quantityBase,
                        'refund_amount'  => $refundAmount,
                    ]);

                    if ($saleItem->batch_id) {
                        $this->batchService->increment($saleItem->batch, $quantityBase);
                    }
                }

                $this->stockService->move(
                    product: $saleItem->product,
                    warehouse: $sale->warehouse,
                    quantity: $quantityBase,
                    type: StockMovementType::SaleReturn,
                    referenceType: SaleReturn::class,
                    referenceId: $saleReturn->id,
                    userId: $userId,
                    notes: "Return against sale {$sale->invoice_number}"
                );

                $saleItem->increment('returned_quantity_base', $quantityBase);
            }

            $saleReturn->update(['total_refund' => $totalRefund]);

            if ($sale->customer_id && $totalRefund > 0) {
                $this->customerLedgerService->record(
                    customer: $sale->customer,
                    type: CustomerTransactionType::SaleReturn,
                    amount: -$totalRefund,
                    referenceType: SaleReturn::class,
                    referenceId: $saleReturn->id,
                    userId: $userId,
                    notes: "Return against sale {$sale->invoice_number}"
                );
            }

            // Cash physically leaving the drawer — tracked whether or not there's a customer to also credit.
            if ($totalRefund > 0 && $cashRegisterSessionId) {
                $this->cashRegisterService->recordMovement(
                    session: CashRegisterSession::findOrFail($cashRegisterSessionId),
                    type: CashRegisterTransactionType::Refund,
                    amount: -$totalRefund,
                    referenceType: SaleReturn::class,
                    referenceId: $saleReturn->id,
                    userId: $userId,
                    notes: "Refund for return against sale {$sale->invoice_number}"
                );
            }

            if ($sale->customer_id && $totalPointsReverse > 0) {
                // If the customer already redeemed more points than remain after this reversal,
                // the resulting negative balance is an accepted business outcome — see plan §84 —
                // it simply means their next redemption is blocked until they earn it back.
                $this->loyaltyService->reverse(
                    $sale->customer,
                    $totalPointsReverse,
                    SaleReturn::class,
                    $saleReturn->id,
                    $userId,
                    "Return against sale {$sale->invoice_number}"
                );
            }

            return $saleReturn;
        });
    }
}
