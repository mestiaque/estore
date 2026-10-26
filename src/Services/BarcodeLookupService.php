<?php

namespace ME\Retail\Services;

use ME\Retail\Enums\SerialStatus;
use ME\Retail\Models\Batch;
use ME\Retail\Models\Product;
use ME\Retail\Models\Serial;

/**
 * Resolves a scanned (or typed) code to what it identifies, for the scan
 * inputs on the POS and Purchase screens. A code is matched, in order, as:
 * product barcode -> weighing-scale label (sale only) -> batch (lot) barcode
 * -> serial/IMEI (sale only) -> SKU.
 *
 * For a sale, a batch-tracked product scanned by its product barcode gets the
 * earliest-expiring lot with stock in the warehouse (FEFO) picked for it.
 */
class BarcodeLookupService
{
    public const CONTEXT_SALE = 'sale';
    public const CONTEXT_PURCHASE = 'purchase';

    public function __construct(protected WeighedBarcodeParser $weighedBarcodeParser)
    {
    }

    /**
     * @return array{match: string, product: Product, batch: ?Batch, serial: ?Serial, quantity: ?float}|null
     *         `quantity` is set only for a scale label: the weighed amount in the product's unit.
     */
    public function find(string $code, string $context, ?int $warehouseId = null): ?array
    {
        $code = trim($code);
        if ($code === '') {
            return null;
        }

        $isSale = $context === self::CONTEXT_SALE;

        $product = $this->sellableProducts()->where('barcode', $code)->first();
        if ($product) {
            $batch = $isSale && $product->track_type === 'batch' ? $this->firstExpiringBatch($product, $warehouseId) : null;

            return $this->result('product', $product, $batch);
        }

        if ($isSale && $weighed = $this->findWeighed($code, $warehouseId)) {
            return $weighed;
        }

        $batch = $this->findBatch($code, $isSale, $warehouseId);
        if ($batch) {
            return $this->result('batch', $batch->product, $batch);
        }

        if ($isSale) {
            $serial = Serial::query()
                ->with('product')
                ->where('serial_number', $code)
                ->where('status', SerialStatus::Available)
                ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
                ->whereHas('product', fn ($q) => $q->where('status', true))
                ->first();

            if ($serial) {
                return $this->result('serial', $serial->product, null, $serial);
            }
        }

        $product = $this->sellableProducts()->where('sku', $code)->first();
        if ($product) {
            $batch = $isSale && $product->track_type === 'batch' ? $this->firstExpiringBatch($product, $warehouseId) : null;

            return $this->result('sku', $product, $batch);
        }

        return null;
    }

    /**
     * @return array{match: string, product: Product, batch: ?Batch, serial: ?Serial, quantity: ?float}|null
     */
    private function findWeighed(string $code, ?int $warehouseId): ?array
    {
        $label = $this->weighedBarcodeParser->parse($code);
        if (!$label) {
            return null;
        }

        $product = $this->sellableProducts()->with('unit')->where('plu_code', $label['item_code'])->first()
            ?? $this->sellableProducts()->with('unit')->where('plu_code', ltrim($label['item_code'], '0'))->first();
        if (!$product) {
            return null;
        }

        $quantity = $this->weighedBarcodeParser->quantityFor($product, $label['value']);
        if (!$quantity) {
            return null;
        }

        $batch = $product->track_type === 'batch' ? $this->firstExpiringBatch($product, $warehouseId) : null;

        return $this->result('weighed', $product, $batch, null, $quantity);
    }

    /**
     * Active products that can appear on an invoice — template products with
     * generated variants are excluded, only the variants themselves are traded.
     */
    private function sellableProducts()
    {
        return Product::query()->where('status', true)->whereDoesntHave('variants');
    }

    /**
     * A sale needs the lot's row in the selling warehouse with stock left; a
     * purchase only needs to know which product/lot the code belongs to.
     */
    private function findBatch(string $code, bool $isSale, ?int $warehouseId): ?Batch
    {
        return Batch::query()
            ->with('product')
            ->where('barcode', $code)
            ->whereHas('product', fn ($q) => $q->where('status', true))
            ->when($isSale, fn ($q) => $q->where('status', true)->where('quantity', '>', 0))
            ->when($isSale && $warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when(!$isSale && $warehouseId, fn ($q) => $q->orderByRaw('warehouse_id = ? desc', [$warehouseId]))
            ->orderBy('id')
            ->first();
    }

    private function firstExpiringBatch(Product $product, ?int $warehouseId): ?Batch
    {
        return Batch::query()
            ->where('product_id', $product->id)
            ->where('status', true)
            ->where('quantity', '>', 0)
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->orderByRaw('expiry_date is null')
            ->orderBy('expiry_date')
            ->orderBy('id')
            ->first();
    }

    /**
     * @return array{match: string, product: Product, batch: ?Batch, serial: ?Serial, quantity: ?float}
     */
    private function result(string $match, Product $product, ?Batch $batch = null, ?Serial $serial = null, ?float $quantity = null): array
    {
        return ['match' => $match, 'product' => $product, 'batch' => $batch, 'serial' => $serial, 'quantity' => $quantity];
    }
}
