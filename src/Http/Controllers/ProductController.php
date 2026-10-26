<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\Brand;
use ME\Retail\Models\Category;
use ME\Retail\Models\Product;
use ME\Retail\Models\Unit;
use ME\Retail\Services\BusinessTypeService;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_product.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_product.create')->only(['create', 'store']);
        $this->middleware('authorization:retail_product.edit')->only(['edit', 'update']);
        $this->middleware('authorization:retail_product.delete')->only('destroy');
    }

    public function index()
    {
        $products = Product::with(['category', 'brand', 'unit'])->latest()->paginate(get_setting('pagination', 10));
        return view('retail::products.index', compact('products'));
    }

    public function create(BusinessTypeService $businessTypes)
    {
        $categories = Category::where('status', true)->get();
        $brands     = Brand::where('status', true)->get();
        $units      = Unit::where('status', true)->get();
        // Tracking type / expiry pre-selected from the shop's business-type preset.
        $productDefaults = $businessTypes->productDefaults();
        return view('retail::products.create', compact('categories', 'brands', 'units', 'productDefaults'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id'      => 'required|exists:retail_categories,id',
            'brand_id'         => 'nullable|exists:retail_brands,id',
            'unit_id'          => 'required|exists:retail_units,id',
            'name'             => 'required|string|max:255',
            'sku'              => 'required|string|max:100|unique:retail_products,sku',
            'barcode'          => 'nullable|string|max:100|unique:retail_products,barcode|unique:retail_batches,barcode',
            'plu_code'         => 'nullable|string|max:20|unique:retail_products,plu_code',
            'purchase_price'   => 'required|numeric|min:0',
            'sale_price'       => 'required|numeric|min:0',
            'wholesale_price'  => 'nullable|numeric|min:0',
            'track_type'       => 'required|in:normal,batch,serial',
            'has_expiry'       => 'nullable|boolean',
            'earn_points'      => 'nullable|numeric|min:0',
            'status'           => 'nullable|boolean',
            'notes'            => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            Product::create([
                'category_id'     => $request->category_id,
                'brand_id'        => $request->brand_id,
                'unit_id'         => $request->unit_id,
                'name'            => $request->name,
                'sku'             => $request->sku,
                'barcode'         => $request->barcode,
                'plu_code'        => $request->plu_code,
                'purchase_price'  => $request->purchase_price,
                'sale_price'      => $request->sale_price,
                'wholesale_price' => $request->wholesale_price,
                'track_type'      => $request->track_type,
                'has_expiry'      => $request->has('has_expiry') ? 1 : 0,
                'earn_points'     => $request->earn_points ?: 0,
                'status'          => $request->has('status') ? 1 : 0,
                'notes'           => $request->notes,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.products.index')->with('error', __('Failed to create product'));
        }

        return redirect()->route('retail.products.index')->with('success', __('Product created successfully'));
    }

    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'unit', 'parent', 'variantValues', 'variants.variantValues']);
        return view('retail::products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('status', true)->get();
        $brands     = Brand::where('status', true)->get();
        $units      = Unit::where('status', true)->get();
        return view('retail::products.edit', compact('product', 'categories', 'brands', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'category_id'      => 'required|exists:retail_categories,id',
            'brand_id'         => 'nullable|exists:retail_brands,id',
            'unit_id'          => 'required|exists:retail_units,id',
            'name'             => 'required|string|max:255',
            'sku'              => 'required|string|max:100|unique:retail_products,sku,' . $product->id,
            'barcode'          => 'nullable|string|max:100|unique:retail_products,barcode,' . $product->id . '|unique:retail_batches,barcode',
            'plu_code'         => 'nullable|string|max:20|unique:retail_products,plu_code,' . $product->id,
            'purchase_price'   => 'required|numeric|min:0',
            'sale_price'       => 'required|numeric|min:0',
            'wholesale_price'  => 'nullable|numeric|min:0',
            'track_type'       => 'required|in:normal,batch,serial',
            'has_expiry'       => 'nullable|boolean',
            'earn_points'      => 'nullable|numeric|min:0',
            'status'           => 'nullable|boolean',
            'notes'            => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $product->update([
                'category_id'     => $request->category_id,
                'brand_id'        => $request->brand_id,
                'unit_id'         => $request->unit_id,
                'name'            => $request->name,
                'sku'             => $request->sku,
                'barcode'         => $request->barcode,
                'plu_code'        => $request->plu_code,
                'purchase_price'  => $request->purchase_price,
                'sale_price'      => $request->sale_price,
                'wholesale_price' => $request->wholesale_price,
                'track_type'      => $request->track_type,
                'has_expiry'      => $request->has('has_expiry') ? 1 : 0,
                'earn_points'     => $request->earn_points ?: 0,
                'status'          => $request->has('status') ? 1 : 0,
                'notes'           => $request->notes,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.products.index')->with('error', __('Failed to update product'));
        }

        return redirect()->route('retail.products.index')->with('success', __('Product updated successfully'));
    }

    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            $product->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.products.index')->with('error', __('Failed to delete product'));
        }

        return redirect()->route('retail.products.index')->with('success', __('Product deleted successfully'));
    }
}
