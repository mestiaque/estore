<?php

namespace ME\Retail\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use ME\Retail\Enums\SerialStatus;
use ME\Retail\Exceptions\DuplicateSerialException;
use ME\Retail\Models\Product;
use ME\Retail\Models\Serial;
use ME\Retail\Models\Warehouse;

/**
 * The single authorized way serial-tracked units are ever created or moved
 * through their lifecycle (available -> sold -> returned -> ...).
 */
class SerialService
{
    /**
     * @param string[] $serialNumbers
     * @param string|null $receivedDate purchase_date the units were received on — the warranty clock start
     * @param int|null $warrantyMonths null/0 means "no warranty tracked" for this line
     * @return Serial[]
     */
    public function createMany(Product $product, Warehouse $warehouse, array $serialNumbers, ?int $purchaseItemId, float $purchasePrice, ?string $receivedDate = null, ?int $warrantyMonths = null): array
    {
        return DB::transaction(function () use ($product, $warehouse, $serialNumbers, $purchaseItemId, $purchasePrice, $receivedDate, $warrantyMonths) {
            $created = [];

            $warrantyStart = $warrantyMonths ? ($receivedDate ?: now()->toDateString()) : null;
            $warrantyEnd   = $warrantyMonths ? \Carbon\Carbon::parse($warrantyStart)->addMonths($warrantyMonths)->toDateString() : null;

            foreach ($serialNumbers as $serialNumber) {
                $serialNumber = trim($serialNumber);

                if ($serialNumber === '') {
                    continue;
                }

                if (Serial::where('product_id', $product->id)->where('serial_number', $serialNumber)->exists()) {
                    throw DuplicateSerialException::forProduct($product->name, $serialNumber);
                }

                $created[] = Serial::create([
                    'product_id'        => $product->id,
                    'warehouse_id'      => $warehouse->id,
                    'purchase_item_id'  => $purchaseItemId,
                    'serial_number'     => $serialNumber,
                    'status'            => SerialStatus::Available,
                    'purchase_price'    => $purchasePrice,
                    'warranty_months'   => $warrantyMonths ?: null,
                    'warranty_start'    => $warrantyStart,
                    'warranty_end'      => $warrantyEnd,
                ]);
            }

            return $created;
        });
    }

    /**
     * @param int[] $serialIds
     * @return Serial[]
     */
    public function reserveForSale(Product $product, array $serialIds, int $saleItemId): array
    {
        return DB::transaction(function () use ($product, $serialIds, $saleItemId) {
            $reserved = [];

            foreach ($serialIds as $serialId) {
                $serial = Serial::query()->whereKey($serialId)->lockForUpdate()->firstOrFail();

                if ($serial->product_id !== $product->id) {
                    throw new InvalidArgumentException("Serial \"{$serial->serial_number}\" does not belong to product \"{$product->name}\".");
                }

                if ($serial->status !== SerialStatus::Available) {
                    throw new InvalidArgumentException("Serial \"{$serial->serial_number}\" is not available for sale (current status: {$serial->status->label()}).");
                }

                $serial->update([
                    'status'       => SerialStatus::Sold,
                    'sale_item_id' => $saleItemId,
                ]);

                $reserved[] = $serial;
            }

            return $reserved;
        });
    }

    public function markReturned(Serial $serial): void
    {
        DB::transaction(function () use ($serial) {
            $locked = Serial::query()->whereKey($serial->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== SerialStatus::Sold) {
                throw new InvalidArgumentException("Serial \"{$locked->serial_number}\" cannot be returned from status {$locked->status->label()}.");
            }

            // Back in salable stock — a damaged unit should be moved to Damaged separately, not through a sale return.
            $locked->update(['status' => SerialStatus::Available]);
            $serial->refresh();
        });
    }
}
