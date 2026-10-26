<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\Attribute;
use ME\Retail\Models\Product;
use ME\Retail\Services\ProductVariantService;

class ProductVariantController extends Controller
{
    public function __construct(protected ProductVariantService $productVariantService)
    {
        $this->middleware('authorization:retail_product.edit');
    }

    public function attributesForm(Product $product)
    {
        $attributes = Attribute::where('status', true)->get();
        $selectedIds = $product->variantAttributes()->pluck('retail_attributes.id')->all();

        return view('retail::products.variant-attributes', compact('product', 'attributes', 'selectedIds'));
    }

    public function attributesStore(Request $request, Product $product)
    {
        $request->validate([
            'attribute_ids'   => 'nullable|array',
            'attribute_ids.*' => 'exists:retail_attributes,id',
        ]);

        DB::beginTransaction();
        try {
            $this->productVariantService->setVariantAttributes($product, $request->attribute_ids ?? []);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.products.variants.attributes-form', $product->id)->with('error', $e->getMessage() ?: __('Failed to save variant attributes'));
        }

        return redirect()->route('retail.products.variants.generate-form', $product->id)->with('success', __('Variant attributes saved — now select which values to generate.'));
    }

    public function generateForm(Product $product)
    {
        $product->load(['variantAttributes.values' => fn ($q) => $q->where('status', true), 'variants.variantValues']);

        return view('retail::products.variant-generate', compact('product'));
    }

    public function generate(Request $request, Product $product)
    {
        $request->validate([
            'values'   => 'required|array',
            'values.*' => 'array',
            'values.*.*' => 'exists:retail_attribute_values,id',
        ]);

        DB::beginTransaction();
        try {
            $created = $this->productVariantService->generate($product, $request->values);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.products.variants.generate-form', $product->id)->with('error', $e->getMessage() ?: __('Failed to generate variants'));
        }

        return redirect()->route('retail.products.show', $product->id)->with('success', __(':count variant(s) generated successfully', ['count' => count($created)]));
    }
}
