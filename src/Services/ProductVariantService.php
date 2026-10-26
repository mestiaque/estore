<?php

namespace ME\Retail\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use ME\Retail\Models\AttributeValue;
use ME\Retail\Models\Product;

/**
 * Generates variant combinations (plan §8: T-Shirt Red/M, Red/L, Blue/M...).
 * Every generated variant is a real retail_products row, so it automatically
 * works with Purchase/Sale/Stock/Batch/Serial — nothing else changes.
 */
class ProductVariantService
{
    public function setVariantAttributes(Product $product, array $attributeIds): void
    {
        if ($product->isVariant()) {
            throw new InvalidArgumentException('A variant product cannot itself define variant attributes.');
        }

        $product->variantAttributes()->sync($attributeIds);
    }

    /**
     * @param array<int, int[]> $selections attribute_id => [attribute_value_id, ...]
     * @return Product[] newly created variants (existing combinations are skipped, not duplicated)
     */
    public function generate(Product $parent, array $selections): array
    {
        if ($parent->isVariant()) {
            throw new InvalidArgumentException('Cannot generate variants for a product that is itself a variant.');
        }

        $selections = array_filter($selections);
        if (empty($selections)) {
            throw new InvalidArgumentException('Select at least one attribute value to generate variants.');
        }

        $combinations = $this->cartesianProduct(array_values($selections));

        return DB::transaction(function () use ($parent, $combinations) {
            $existingCombos = $parent->variants()
                ->with('variantValues')
                ->get()
                ->map(fn (Product $variant) => $variant->variantValues->pluck('id')->sort()->values()->all());

            $created = [];

            foreach ($combinations as $combo) {
                sort($combo);

                $alreadyExists = $existingCombos->contains(fn ($existing) => $existing === $combo);
                if ($alreadyExists) {
                    continue;
                }

                $values = AttributeValue::whereIn('id', $combo)->get();
                $suffix = $values->map(fn (AttributeValue $v) => Str::upper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $v->value), 0, 3)))->implode('-');

                $variant = Product::create([
                    'parent_product_id' => $parent->id,
                    'category_id'       => $parent->category_id,
                    'brand_id'          => $parent->brand_id,
                    'unit_id'           => $parent->unit_id,
                    'name'              => $parent->name . ' - ' . $values->pluck('value')->implode(' / '),
                    'sku'               => $parent->sku . '-' . $suffix . '-' . Str::upper(Str::random(3)),
                    'purchase_price'    => $parent->purchase_price,
                    'sale_price'        => $parent->sale_price,
                    'wholesale_price'   => $parent->wholesale_price,
                    'track_type'        => $parent->track_type,
                    'has_expiry'        => $parent->has_expiry,
                    'earn_points'       => $parent->earn_points,
                    'status'            => true,
                ]);

                $variant->variantValues()->sync($combo);
                $created[] = $variant;
            }

            return $created;
        });
    }

    /**
     * @param array<int, int[]> $groups
     * @return array<int, int[]>
     */
    private function cartesianProduct(array $groups): array
    {
        $result = [[]];

        foreach ($groups as $group) {
            $append = [];
            foreach ($result as $combo) {
                foreach ($group as $value) {
                    $append[] = [...$combo, $value];
                }
            }
            $result = $append;
        }

        return $result;
    }
}
