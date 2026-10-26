<?php

namespace ME\Retail\Tests\Feature;

use ME\Retail\Models\Attribute;
use ME\Retail\Models\Product;
use ME\Retail\Tests\TestCase;

class ProductVariantFlowTest extends TestCase
{
    use CreatesCatalog;

    public function test_generating_variants_creates_one_real_product_per_combination(): void
    {
        $product = $this->createProduct(['name' => 'T-Shirt', 'sku' => 'TSHIRT']);

        $color = Attribute::create(['name' => 'Color', 'status' => true]);
        $red   = $color->values()->create(['value' => 'Red', 'status' => true]);
        $blue  = $color->values()->create(['value' => 'Blue', 'status' => true]);

        $size = Attribute::create(['name' => 'Size', 'status' => true]);
        $m    = $size->values()->create(['value' => 'M', 'status' => true]);
        $l    = $size->values()->create(['value' => 'L', 'status' => true]);

        $this->actingAs($this->admin())->post(route('retail.products.variants.attributes-store', $product->id), [
            'attribute_ids' => [$color->id, $size->id],
        ]);

        $this->actingAs($this->admin())->post(route('retail.products.variants.generate', $product->id), [
            'values' => [
                $color->id => [$red->id, $blue->id],
                $size->id  => [$m->id, $l->id],
            ],
        ]);

        // 2 colors x 2 sizes = 4 variants.
        $this->assertSame(4, Product::where('parent_product_id', $product->id)->count());

        $variant = Product::where('parent_product_id', $product->id)
            ->whereHas('variantValues', fn ($q) => $q->where('retail_attribute_values.id', $red->id))
            ->whereHas('variantValues', fn ($q) => $q->where('retail_attribute_values.id', $m->id))
            ->first();

        $this->assertNotNull($variant, 'a Red/M variant should exist');
        $this->assertTrue($variant->isVariant());
        $this->assertSame($product->sale_price->value ?? (string) $product->sale_price, (string) $variant->sale_price, 'variant inherits the parent price by default');

        // Re-generating with the same selection must not create duplicates.
        $this->actingAs($this->admin())->post(route('retail.products.variants.generate', $product->id), [
            'values' => [
                $color->id => [$red->id, $blue->id],
                $size->id  => [$m->id, $l->id],
            ],
        ]);

        $this->assertSame(4, Product::where('parent_product_id', $product->id)->count(), 're-generating the same combinations must not duplicate them');
    }
}
