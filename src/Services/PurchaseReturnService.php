<?php

namespace ME\Retail\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use ME\Retail\Enums\SerialStatus;
use ME\Retail\Enums\StockMovementType;
use ME\Retail\Enums\SupplierTransactionType;
use ME\Retail\Models\Purchase;
use ME\Retail\Models\PurchaseItem;
use ME\Retail\Models\PurchaseReturn;
use ME\Retail\Models\PurchaseReturnItem;
use ME\Retail\Models\Serial;

/**
 * Purchase Return flow: Purchase -> Validate Returnable Quantity -> Stock
 * Decrease -> Supplier Credit, all inside a single atomic transaction. Never
 * allows returning more than was purchased minus what was already returned.
 */
class PurchaseReturnService
{
    public function __construct(
        protected StockService $stockService,
        protected SupplierLedgerService $supplierLedgerService,
        protected BatchService $batchService
    ) {
    }

    /**
     * @param array<int, array{purchase_item_id:int, quantity:float, serial_ids?:int[]}> $items
     */
    public function create(Purchase $purchase, array $items, ?string $notes, ?int $userId): PurchaseReturn
    {
        return DB::transaction(function () use ($purchase, $items, $notes, $userId) {
            $purchaseReturn = PurchaseReturn::create([
                'purchase_id'  => $purchase->id,
                'supplier_id'  => $purchase->supplier_id,
                'warehouse_id' => $purchase->warehouse_id,
                'return_date'  => now()->toDateString(),
                'total_refund' => 0,
                'notes'        => $notes,
                'user_id'      => $userId,
            ]);

            $totalRefund = 0.0;

            foreach ($items as $item) {
                $purchaseItem = PurchaseItem::where('purchase_id', $purchase->id)->findOrFail($item['purchase_item_id']);
                $isSerialTracked = $purchaseItem->product->track_type === 'serial';

                $serialIds = $isSerialTracked ? array_values(array_filter($item['serial_ids'] ?? [])) : [];
                $quantity  = $isSerialTracked ? (float) count($serialIds) : (float) ($item['quantity'] ?? 0);

                if ($quantity <= 0) {
                    continue;
                }

                $ratio        = (float) $purchaseItem->quantity > 0 ? (float) $purchaseItem->quantity_base / (float) $purchaseItem->quantity : 0;
                $quantityBase = $quantity * $ratio;
                $remaining    = $purchaseItem->remainingReturnableBaseQuantity();

                if ($quantityBase > $remaining + 0.0001) {
                    throw new InvalidArgumentException(
                        "Cannot return {$quantity} of \"{$purchaseItem->product->name}\" — only " .
                        ($ratio > 0 ? round($remaining / $ratio, 4) : 0) . ' remains returnable.'
                    );
                }

                $unitCostRate = (float) $purchaseItem->quantity > 0 ? (float) $purchaseItem->total / (float) $purchaseItem->quantity : 0;

                if ($isSerialTracked) {
                    // Only unsold stock can go back to the supplier — one return item per serial for a clean audit trail.
                    foreach ($serialIds as $serialId) {
                        $serial = Serial::query()->whereKey($serialId)->firstOrFail();

                        if ($serial->product_id !== $purchaseItem->product_id || $serial->purchase_item_id !== $purchaseItem->id) {
                            throw new InvalidArgumentException("Serial \"{$serial->serial_number}\" was not received on this purchase item.");
                        }
                        if ($serial->status !== SerialStatus::Available) {
                            throw new InvalidArgumentException("Serial \"{$serial->serial_number}\" cannot be returned from status {$serial->status->label()}.");
                        }

                        $refundAmount = $unitCostRate;
                        $totalRefund += $refundAmount;

                        PurchaseReturnItem::create([
                            'purchase_return_id' => $purchaseReturn->id,
                            'purchase_item_id'   => $purchaseItem->id,
                            'product_id'         => $purchaseItem->product_id,
                            'serial_id'          => $serial->id,
                            'quantity'           => 1,
                            'quantity_base'      => $ratio,
                            'refund_amount'      => $refundAmount,
                        ]);

                        $serial->update(['status' => SerialStatus::ReturnedToSupplier]);
                    }
                } else {
                    $refundAmount = $quantity * $unitCostRate;
                    $totalRefund += $refundAmount;

                    PurchaseReturnItem::create([
                        'purchase_return_id' => $purchaseReturn->id,
                        'purchase_item_id'   => $purchaseItem->id,
                        'product_id'         => $purchaseItem->product_id,
                        'quantity'           => $quantity,
                        'quantity_base'      => $quantityBase,
                        'refund_amount'      => $refundAmount,
                    ]);

                    if ($purchaseItem->batch_id) {
                        $this->batchService->decrement($purchaseItem->batch, $quantityBase);
                    }
                }

                $this->stockService->move(
                    product: $purchaseItem->product,
                    warehouse: $purchase->warehouse,
                    quantity: -$quantityBase,
                    type: StockMovementType::PurchaseReturn,
                    referenceType: PurchaseReturn::class,
                    referenceId: $purchaseReturn->id,
                    userId: $userId,
                    notes: "Return against purchase {$purchase->invoice_number}"
                );

                $purchaseItem->increment('returned_quantity_base', $quantityBase);
            }

            $purchaseReturn->update(['total_refund' => $totalRefund]);

            if ($totalRefund > 0) {
                $this->supplierLedgerService->record(
                    supplier: $purchase->supplier,
                    type: SupplierTransactionType::PurchaseReturn,
                    amount: -$totalRefund,
                    referenceType: PurchaseReturn::class,
                    referenceId: $purchaseReturn->id,
                    userId: $userId,
                    notes: "Return against purchase {$purchase->invoice_number}"
                );
            }

            return $purchaseReturn;
        });
    }
}
