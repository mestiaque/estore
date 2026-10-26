<?php

namespace ME\Retail\Services;

use Illuminate\Support\Facades\DB;
use ME\Retail\Exceptions\InsufficientBatchStockException;
use ME\Retail\Models\Batch;
use ME\Retail\Models\Product;
use ME\Retail\Models\Warehouse;

/**
 * The single authorized way batch quantities are ever mutated. A batch's
 * quantity is a finer-grained breakdown of the same total StockService
 * tracks at product+warehouse level — the two must always stay consistent,
 * which is why every batch/serial write happens inside the same transaction
 * as the corresponding StockService::move() call in Purchase/Sale/Return.
 */
class BatchService
{
    public function findOrCreate(Product $product, Warehouse $warehouse, string $batchNumber, ?string $expiryDate, float $purchasePrice): Batch
    {
        return DB::transaction(function () use ($product, $warehouse, $batchNumber, $expiryDate, $purchasePrice) {
            $batch = Batch::query()
                ->where('product_id', $product->id)
                ->where('warehouse_id', $warehouse->id)
                ->where('batch_number', $batchNumber)
                ->lockForUpdate()
                ->first();

            if ($batch) {
                return $batch;
            }

            return Batch::create([
                'product_id'      => $product->id,
                'warehouse_id'    => $warehouse->id,
                'batch_number'    => $batchNumber,
                'expiry_date'     => $expiryDate,
                'purchase_price'  => $purchasePrice,
                'quantity'        => 0,
            ]);
        });
    }

    public function increment(Batch $batch, float $quantity): void
    {
        DB::transaction(function () use ($batch, $quantity) {
            Batch::query()->whereKey($batch->id)->lockForUpdate()->increment('quantity', $quantity);
            $batch->refresh();
        });
    }

    public function decrement(Batch $batch, float $quantity): void
    {
        DB::transaction(function () use ($batch, $quantity) {
            $locked = Batch::query()->whereKey($batch->id)->lockForUpdate()->firstOrFail();

            if ((float) $locked->quantity < $quantity) {
                throw InsufficientBatchStockException::forBatch(
                    $locked->product->name,
                    $locked->batch_number,
                    $quantity,
                    (float) $locked->quantity
                );
            }

            $locked->decrement('quantity', $quantity);
            $batch->refresh();
        });
    }
}
