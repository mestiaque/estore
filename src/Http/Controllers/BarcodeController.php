<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\Batch;
use ME\Retail\Models\Product;
use ME\Retail\Services\BarcodeLookupService;
use ME\Retail\Services\BarcodeService;
use ME\Retail\Support\RetailSettings;

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

    /**
     * Fills in every missing product barcode and every missing batch (lot) barcode.
     */
    public function bulkGenerate()
    {
        DB::beginTransaction();
        try {
            $count = $this->barcodeService->assignMissing() + $this->barcodeService->assignMissingBatches();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.products.index')->with('error', __('Failed to generate barcodes'));
        }

        return redirect()->route('retail.products.index')->with('success', __(':count barcode(s) generated successfully', ['count' => $count]));
    }

    /**
     * Resolves a scanned code for the POS (context=sale) or Purchase (context=purchase)
     * scan input. Authorized by that screen's own create permission, since that is the
     * only place it is used.
     */
    public function lookup(Request $request, BarcodeLookupService $lookupService)
    {
        $request->validate([
            'code'         => 'required|string|max:255',
            'context'      => 'required|in:sale,purchase',
            'warehouse_id' => 'nullable|integer',
        ]);

        abort_unless(auth()->user()->hasPermission("retail_{$request->context}.create"), 403);

        $result = $lookupService->find($request->code, $request->context, $request->integer('warehouse_id') ?: null);

        if (!$result) {
            return response()->json([
                'found'   => false,
                'message' => __('Nothing found for ":code"', ['code' => $request->code]),
            ], 404);
        }

        $product = $result['product'];
        $batch   = $result['batch'];
        $serial  = $result['serial'];

        return response()->json([
            'found'    => true,
            'match'    => $result['match'],
            'quantity' => $result['quantity'],
            'product' => [
                'id'         => $product->id,
                'name'       => $product->name,
                'track_type' => $product->track_type,
            ],
            'batch' => $batch ? [
                'id'           => $batch->id,
                'batch_number' => $batch->batch_number,
                'barcode'      => $batch->barcode,
                'expiry_date'  => $batch->expiry_date?->format('Y-m-d'),
                'quantity'     => (float) $batch->quantity,
                'warehouse_id' => $batch->warehouse_id,
            ] : null,
            'serial' => $serial ? [
                'id'            => $serial->id,
                'serial_number' => $serial->serial_number,
            ] : null,
        ]);
    }

    public function labelForm()
    {
        $products = Product::where('status', true)->orderBy('name')->get();
        $batches  = Batch::with(['product', 'warehouse'])
            ->where('status', true)
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date')
            ->get();
        $sizes    = (RetailSettings::get('label_sizes') ?: []);

        return view('retail::barcode-labels.form', compact('products', 'batches', 'sizes'));
    }

    public function labelPrint(Request $request)
    {
        $request->validate([
            'products'            => 'required_without:batches|array',
            'products.*.id'      => 'required|exists:retail_products,id',
            'products.*.quantity' => 'required|integer|min:1|max:500',
            'batches'             => 'required_without:products|array',
            'batches.*.id'       => 'required|exists:retail_batches,id',
            'batches.*.quantity'  => 'required|integer|min:1|max:500',
            'size'                 => 'required|string',
            'custom_width'        => 'nullable|numeric|min:10',
            'custom_height'       => 'nullable|numeric|min:10',
        ]);

        $sizes = (RetailSettings::get('label_sizes') ?: []);
        if ($request->size === 'custom') {
            $dimensions = ['width' => (float) $request->custom_width, 'height' => (float) $request->custom_height];
        } else {
            $dimensions = $sizes[$request->size] ?? reset($sizes);
        }

        $products = Product::whereIn('id', collect($request->products)->pluck('id'))->get()->keyBy('id');
        $batches  = Batch::with('product')->whereIn('id', collect($request->batches)->pluck('id'))->get()->keyBy('id');

        $productLabels = collect($request->products)->flatMap(function ($row) use ($products) {
            $product = $products->get($row['id']);
            if (!$product) {
                return [];
            }

            return array_fill(0, (int) $row['quantity'], [
                'name'  => $product->name,
                'code'  => $product->barcode ?? $product->sku,
                'price' => $product->sale_price,
                'meta'  => null,
            ]);
        });

        // A lot without its own barcode falls back to the product's code, so the label still scans.
        $batchLabels = collect($request->batches)->flatMap(function ($row) use ($batches) {
            $batch = $batches->get($row['id']);
            if (!$batch) {
                return [];
            }

            return array_fill(0, (int) $row['quantity'], [
                'name'  => $batch->product->name,
                'code'  => $batch->barcode ?: ($batch->product->barcode ?? $batch->product->sku),
                'price' => $batch->product->sale_price,
                'meta'  => trim(__('Batch') . ' ' . $batch->batch_number . ($batch->expiry_date ? ' · ' . __('Exp') . ' ' . $batch->expiry_date->format('d/m/Y') : '')),
            ]);
        });

        $labels = $productLabels->concat($batchLabels);

        return view('retail::barcode-labels.print', compact('labels', 'dimensions'));
    }
}
