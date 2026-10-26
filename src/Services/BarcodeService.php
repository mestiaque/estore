<?php

namespace ME\Retail\Services;

use Illuminate\Support\Facades\DB;
use ME\Retail\Exceptions\DuplicateBarcodeException;
use ME\Retail\Models\Batch;
use ME\Retail\Models\Product;
use ME\Retail\Support\RetailModules;
use ME\Retail\Support\RetailSettings;

/**
 * Generated-barcode strategy (plan §9) — the only place the format is read,
 * so a shop that wants a different scheme replaces this one class. Existing
 * manufacturer barcodes are never touched; this only fills in products that
 * don't have one.
 *
 * Product barcodes and batch (lot) barcodes share one namespace: a scanned
 * code must resolve to exactly one thing, so a code used by a product can
 * never be a batch barcode and vice versa.
 */
class BarcodeService
{
    /**
     * A product's own id is already unique, so basing the generated code on
     * it guarantees uniqueness without a separate counter/lock.
     */
    public function codeFor(Product $product): string
    {
        $prefix = RetailSettings::get('barcode_prefix');
        $length = RetailSettings::get('barcode_number_length');

        return $prefix . '-' . str_pad((string) $product->id, $length, '0', STR_PAD_LEFT);
    }

    public function codeForBatch(Batch $batch): string
    {
        $prefix = RetailSettings::get('batch_barcode_prefix');
        $length = RetailSettings::get('barcode_number_length');

        return $prefix . '-' . str_pad((string) $batch->id, $length, '0', STR_PAD_LEFT);
    }

    public function autoGeneratesBatchBarcodes(): bool
    {
        return RetailModules::enabled('auto_batch_barcode');
    }

    public function assign(Product $product, bool $force = false): Product
    {
        if ($product->barcode && !$force) {
            return $product;
        }

        return DB::transaction(function () use ($product) {
            $code = $this->codeFor($product);

            $exists = Product::where('barcode', $code)->where('id', '!=', $product->id)->exists()
                || Batch::where('barcode', $code)->exists();
            if ($exists) {
                throw DuplicateBarcodeException::forCode($code);
            }

            $product->update(['barcode' => $code]);

            return $product;
        });
    }

    /**
     * Assigns a barcode to every product missing one. Returns how many were assigned.
     */
    public function assignMissing(): int
    {
        $count = 0;

        Product::whereNull('barcode')->orWhere('barcode', '')->each(function (Product $product) use (&$count) {
            $this->assign($product);
            $count++;
        });

        return $count;
    }

    /**
     * Gives a batch a generated barcode, unless it already has one. Rows of the
     * same lot in other warehouses receive the same code so the lot scans the
     * same everywhere.
     */
    public function assignBatch(Batch $batch): Batch
    {
        if ($batch->barcode) {
            return $batch;
        }

        return DB::transaction(function () use ($batch) {
            $code = $this->codeForBatch($batch);
            $this->ensureAvailableForBatch($code, $batch->product_id, $batch->batch_number);

            Batch::query()
                ->where('product_id', $batch->product_id)
                ->where('batch_number', $batch->batch_number)
                ->where(fn ($q) => $q->whereNull('barcode')->orWhere('barcode', ''))
                ->update(['barcode' => $code]);

            return $batch->refresh();
        });
    }

    /**
     * Assigns a barcode to every batch missing one. Returns how many lots were assigned.
     */
    public function assignMissingBatches(): int
    {
        $count = 0;

        Batch::where(fn ($q) => $q->whereNull('barcode')->orWhere('barcode', ''))->orderBy('id')->each(function (Batch $batch) use (&$count) {
            // An earlier iteration may already have filled this row via a sibling warehouse's lot.
            if ($batch->refresh()->barcode) {
                return;
            }

            $this->assignBatch($batch);
            $count++;
        });

        return $count;
    }

    /**
     * A batch barcode may be shared only by rows of the same lot (same product
     * and batch number, in any warehouse) — never by a product or another lot.
     *
     * @throws DuplicateBarcodeException
     */
    public function ensureAvailableForBatch(string $code, int $productId, string $batchNumber): void
    {
        $usedByProduct = Product::withTrashed()->where('barcode', $code)->exists();

        $usedByOtherLot = Batch::query()
            ->where('barcode', $code)
            ->where(fn ($q) => $q->where('product_id', '!=', $productId)->orWhere('batch_number', '!=', $batchNumber))
            ->exists();

        if ($usedByProduct || $usedByOtherLot) {
            throw DuplicateBarcodeException::forBatchCode($code);
        }
    }
}
