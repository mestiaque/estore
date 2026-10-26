<?php

namespace ME\Retail\Services;

use Illuminate\Support\Facades\DB;
use ME\Retail\Exceptions\DuplicateBarcodeException;
use ME\Retail\Models\Product;

/**
 * Generated-barcode strategy (plan §9) — the only place the format is read,
 * so a shop that wants a different scheme replaces this one class. Existing
 * manufacturer barcodes are never touched; this only fills in products that
 * don't have one.
 */
class BarcodeService
{
    /**
     * A product's own id is already unique, so basing the generated code on
     * it guarantees uniqueness without a separate counter/lock.
     */
    public function codeFor(Product $product): string
    {
        $prefix = config('retail.barcode_prefix', 'STORE');
        $length = config('retail.barcode_number_length', 6);

        return $prefix . '-' . str_pad((string) $product->id, $length, '0', STR_PAD_LEFT);
    }

    public function assign(Product $product, bool $force = false): Product
    {
        if ($product->barcode && !$force) {
            return $product;
        }

        return DB::transaction(function () use ($product) {
            $code = $this->codeFor($product);

            $exists = Product::where('barcode', $code)->where('id', '!=', $product->id)->exists();
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
}
