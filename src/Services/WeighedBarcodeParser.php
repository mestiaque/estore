<?php

namespace ME\Retail\Services;

use ME\Retail\Models\Product;
use ME\Retail\Support\RetailModules;
use ME\Retail\Support\RetailSettings;

/**
 * Reads the barcodes printed by label-printing weighing scales for loose goods
 * (fish, meat, vegetables): {prefix}{item code}{value}{check digit}, where the
 * item code is a product's PLU code and the value is either the weight in grams
 * or the price in the smallest currency unit. Layout comes from
 * RetailSettings (weighed_barcode.*, editable on the Retail Settings screen); it is
 * on/off as the `weighed_barcode` feature.
 */
class WeighedBarcodeParser
{
    /**
     * @return array{enabled: bool, prefixes: string[], item_code_length: int, value_length: int, value_type: string}
     */
    public function settings(): array
    {
        return [
            'enabled'          => RetailModules::enabled('weighed_barcode'),
            'prefixes'         => RetailSettings::get('weighed_barcode.prefixes') ?: [],
            'item_code_length' => RetailSettings::get('weighed_barcode.item_code_length'),
            'value_length'     => RetailSettings::get('weighed_barcode.value_length'),
            'value_type'       => RetailSettings::get('weighed_barcode.value_type') === 'price' ? 'price' : 'weight',
        ];
    }

    /**
     * @return array{item_code: string, value: int}|null Null when the code is not a valid scale barcode.
     */
    public function parse(string $code): ?array
    {
        $settings = $this->settings();

        if (!$settings['enabled'] || !ctype_digit($code)) {
            return null;
        }

        foreach ($settings['prefixes'] as $prefix) {
            $expectedLength = strlen($prefix) + $settings['item_code_length'] + $settings['value_length'] + 1;

            if (strlen($code) !== $expectedLength || !str_starts_with($code, $prefix) || !$this->hasValidCheckDigit($code)) {
                continue;
            }

            return [
                'item_code' => substr($code, strlen($prefix), $settings['item_code_length']),
                'value'     => (int) substr($code, strlen($prefix) + $settings['item_code_length'], $settings['value_length']),
            ];
        }

        return null;
    }

    /**
     * The quantity, in the product's own unit, that a scale label stands for. A weight
     * label is in grams: it becomes kilograms unless the product itself is sold per gram.
     * A price label is divided by the product's sale price.
     */
    public function quantityFor(Product $product, int $value): ?float
    {
        if ($this->settings()['value_type'] === 'price') {
            $salePrice = (float) $product->sale_price;

            return $salePrice > 0 ? round(($value / 100) / $salePrice, 4) : null;
        }

        $soldPerGram = in_array(strtolower((string) $product->unit?->short_name), ['g', 'gm', 'gram'], true);

        return $soldPerGram ? (float) $value : round($value / 1000, 4);
    }

    /**
     * GTIN/EAN check digit: weights 3,1,3,1… from the digit left of the check digit.
     */
    private function hasValidCheckDigit(string $code): bool
    {
        $digits = array_map('intval', str_split(substr($code, 0, -1)));
        $sum = 0;

        foreach (array_reverse($digits) as $index => $digit) {
            $sum += $digit * ($index % 2 === 0 ? 3 : 1);
        }

        return (10 - $sum % 10) % 10 === (int) substr($code, -1);
    }
}
