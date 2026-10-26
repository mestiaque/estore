<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\Product;
use ME\Retail\Services\BarcodeService;

class BarcodeController extends Controller
{
    public function __construct(protected BarcodeService $barcodeService)
    {
        $this->middleware('authorization:retail_barcode.generate')->only(['generate', 'bulkGenerate']);
        $this->middleware('authorization:retail_barcode.print')->only(['labelForm', 'labelPrint']);
    }

    public function generate(Product $product)
    {
        DB::beginTransaction();
        try {
            $this->barcodeService->assign($product);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage() ?: __('Failed to generate barcode'));
        }

        return redirect()->back()->with('success', __('Barcode generated successfully'));
    }

    public function bulkGenerate()
    {
        DB::beginTransaction();
        try {
            $count = $this->barcodeService->assignMissing();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.products.index')->with('error', __('Failed to generate barcodes'));
        }

        return redirect()->route('retail.products.index')->with('success', __(':count barcode(s) generated successfully', ['count' => $count]));
    }

    public function labelForm()
    {
        $products = Product::where('status', true)->orderBy('name')->get();
        $sizes    = config('retail.label_sizes', []);

        return view('retail::barcode-labels.form', compact('products', 'sizes'));
    }

    public function labelPrint(Request $request)
    {
        $request->validate([
            'products'            => 'required|array|min:1',
            'products.*.id'      => 'required|exists:retail_products,id',
            'products.*.quantity' => 'required|integer|min:1|max:500',
            'size'                 => 'required|string',
            'custom_width'        => 'nullable|numeric|min:10',
            'custom_height'       => 'nullable|numeric|min:10',
        ]);

        $sizes = config('retail.label_sizes', []);
        if ($request->size === 'custom') {
            $dimensions = ['width' => (float) $request->custom_width, 'height' => (float) $request->custom_height];
        } else {
            $dimensions = $sizes[$request->size] ?? reset($sizes);
        }

        $products = Product::whereIn('id', collect($request->products)->pluck('id'))->get()->keyBy('id');

        $labels = collect($request->products)->flatMap(function ($row) use ($products) {
            $product = $products->get($row['id']);
            if (!$product) {
                return [];
            }

            return array_fill(0, (int) $row['quantity'], $product);
        });

        return view('retail::barcode-labels.print', compact('labels', 'dimensions'));
    }
}
