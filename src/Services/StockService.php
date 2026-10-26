<?php

namespace ME\Retail\Services;

use Illuminate\Support\Facades\DB;
use ME\Retail\Enums\StockMovementType;
use ME\Retail\Exceptions\InsufficientStockException;
use ME\Retail\Models\Product;
use ME\Retail\Models\ProductStock;
use ME\Retail\Models\StockLedger;
use ME\Retail\Models\Warehouse;
use ME\Retail\Support\RetailSettings;

/**
 * The single authorized way stock is ever mutated. Every write goes through
 * an append-only ledger entry plus a synchronized per-warehouse summary row;
 * nothing else in the package should touch retail_product_stocks directly.
 */
class StockService
{
    /**
     * @param float $quantity Signed, in the product's base unit. Positive increases stock, negative decreases it.
     */
    public function move(
        Product $product,
        Warehouse $warehouse,
        float $quantity,
        StockMovementType $type,
        ?string $referenceType = null,
        int|string|null $referenceId = null,
        ?int $userId = null,
        ?string $notes = null
    ): StockLedger {
        return DB::transaction(function () use ($product, $warehouse, $quantity, $type, $referenceType, $referenceId, $userId, $notes) {
            $summary = ProductStock::query()
                ->where('product_id', $product->id)
                ->where('warehouse_id', $warehouse->id)
                ->lockForUpdate()
                ->first();

            $before = $summary ? (float) $summary->quantity : 0.0;
            $after  = $before + $quantity;

            if ($after < 0 && !RetailSettings::get('allow_negative_stock')) {
                throw InsufficientStockException::forProduct($product->name, $warehouse->name, abs($quantity), $before);
            }

            if ($summary) {
                $summary->update(['quantity' => $after]);
            } else {
                ProductStock::create([
                    'product_id'   => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity'     => $after,
                ]);
            }

            $ledger = StockLedger::create([
                'product_id'      => $product->id,
                'warehouse_id'    => $warehouse->id,
                'movement_type'   => $type,
                'quantity'        => $quantity,
                'quantity_before' => $before,
                'quantity_after'  => $after,
                'reference_type'  => $referenceType,
                'reference_id'    => $referenceId,
                'user_id'         => $userId,
                'notes'           => $notes,
            ]);

            // Cached total across all warehouses, kept in sync for simple listing/reporting use.
            $product->increment('stock_quantity', $quantity);

            return $ledger;
        });
    }

    public function currentQuantity(Product $product, Warehouse $warehouse): float
    {
        $summary = ProductStock::query()
            ->where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->first();

        return $summary ? (float) $summary->quantity : 0.0;
    }
}
