<?php

namespace ME\Retail\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use ME\Retail\Enums\SerialStatus;
use ME\Retail\Enums\StockMovementType;
use ME\Retail\Models\Batch;
use ME\Retail\Models\Product;
use ME\Retail\Models\Serial;
use ME\Retail\Models\StockTransfer;
use ME\Retail\Models\StockTransferItem;
use ME\Retail\Models\Unit;
use ME\Retail\Models\Warehouse;

/**
 * Stock Transfer: Warehouse A -> Warehouse B, executed as a single atomic
 * move (deduct source, add destination) rather than a multi-step in-transit
 * workflow — simpler, but still fully audited via the stock ledger and
 * impossible to double-deduct since both legs share one DB transaction.
 */
class StockTransferService
{
    public function __construct(
        protected StockService $stockService,
        protected BatchService $batchService
    ) {
    }

    /**
     * @param array{
     *   from_warehouse_id:int, to_warehouse_id:int, transfer_date:string, notes?:string,
     *   items: array<int, array{product_id:int, unit_id:int, quantity:float, source_batch_id?:int, serial_ids?:int[]}>
     * } $data
     */
    public function create(array $data, ?int $userId): StockTransfer
    {
        return DB::transaction(function () use ($data, $userId) {
            if ($data['from_warehouse_id'] == $data['to_warehouse_id']) {
                throw new InvalidArgumentException('Source and destination warehouse must be different.');
            }

            $fromWarehouse = Warehouse::findOrFail($data['from_warehouse_id']);
            $toWarehouse   = Warehouse::findOrFail($data['to_warehouse_id']);

            $transfer = StockTransfer::create([
                'from_warehouse_id' => $fromWarehouse->id,
                'to_warehouse_id'   => $toWarehouse->id,
                'transfer_number'   => $this->generateTransferNumber(),
                'transfer_date'     => $data['transfer_date'],
                'notes'             => $data['notes'] ?? null,
                'user_id'           => $userId,
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $unit    = Unit::findOrFail($item['unit_id']);

                if ($unit->id !== $product->unit_id && $unit->base_unit_id !== $product->unit_id) {
                    throw new InvalidArgumentException(
                        "Unit \"{$unit->name}\" is not a valid transfer unit for product \"{$product->name}\"."
                    );
                }

                $quantity     = (float) $item['quantity'];
                $factor       = (float) $unit->conversion_factor ?: 1.0;
                $quantityBase = $quantity * $factor;

                $sourceBatch = null;
                $destinationBatch = null;

                if ($product->track_type === 'batch') {
                    if (empty($item['source_batch_id'])) {
                        throw new InvalidArgumentException("Select a source batch for product \"{$product->name}\".");
                    }

                    $sourceBatch = Batch::findOrFail($item['source_batch_id']);
                    if ($sourceBatch->product_id !== $product->id || $sourceBatch->warehouse_id !== $fromWarehouse->id) {
                        throw new InvalidArgumentException("Batch does not belong to product \"{$product->name}\" in the source warehouse.");
                    }

                    $this->batchService->decrement($sourceBatch, $quantityBase);

                    $destinationBatch = $this->batchService->findOrCreate(
                        $product,
                        $toWarehouse,
                        $sourceBatch->batch_number,
                        $sourceBatch->expiry_date?->toDateString(),
                        (float) $sourceBatch->purchase_price
                    );
                    $this->batchService->increment($destinationBatch, $quantityBase);
                }

                $serialIds = [];
                if ($product->track_type === 'serial') {
                    $serialIds = array_values(array_filter($item['serial_ids'] ?? []));

                    if (abs($quantityBase - round($quantityBase)) > 0.0001 || count($serialIds) !== (int) round($quantityBase)) {
                        throw new InvalidArgumentException(
                            "Product \"{$product->name}\" is serial-tracked: select exactly {$quantityBase} serial(s)."
                        );
                    }

                    foreach ($serialIds as $serialId) {
                        $serial = Serial::query()->whereKey($serialId)->lockForUpdate()->firstOrFail();

                        if ($serial->product_id !== $product->id || $serial->warehouse_id !== $fromWarehouse->id) {
                            throw new InvalidArgumentException("Serial \"{$serial->serial_number}\" is not available in the source warehouse.");
                        }
                        if ($serial->status !== SerialStatus::Available) {
                            throw new InvalidArgumentException("Serial \"{$serial->serial_number}\" cannot be transferred from status {$serial->status->label()}.");
                        }

                        $serial->update(['warehouse_id' => $toWarehouse->id]);
                    }
                }

                StockTransferItem::create([
                    'stock_transfer_id'     => $transfer->id,
                    'product_id'            => $product->id,
                    'unit_id'               => $unit->id,
                    'source_batch_id'       => $sourceBatch?->id,
                    'destination_batch_id'  => $destinationBatch?->id,
                    'quantity'              => $quantity,
                    'quantity_base'         => $quantityBase,
                ]);

                $this->stockService->move(
                    product: $product,
                    warehouse: $fromWarehouse,
                    quantity: -$quantityBase,
                    type: StockMovementType::TransferOut,
                    referenceType: StockTransfer::class,
                    referenceId: $transfer->id,
                    userId: $userId,
                    notes: "Transfer {$transfer->transfer_number} to {$toWarehouse->name}"
                );

                $this->stockService->move(
                    product: $product,
                    warehouse: $toWarehouse,
                    quantity: $quantityBase,
                    type: StockMovementType::TransferIn,
                    referenceType: StockTransfer::class,
                    referenceId: $transfer->id,
                    userId: $userId,
                    notes: "Transfer {$transfer->transfer_number} from {$fromWarehouse->name}"
                );
            }

            return $transfer;
        });
    }

    private function generateTransferNumber(): string
    {
        $next = (int) (StockTransfer::query()->lockForUpdate()->max('id')) + 1;

        return 'TRF-' . now()->format('Y') . '-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
