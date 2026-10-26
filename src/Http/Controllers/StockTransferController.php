<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Enums\SerialStatus;
use ME\Retail\Models\Batch;
use ME\Retail\Models\Product;
use ME\Retail\Models\Serial;
use ME\Retail\Models\StockTransfer;
use ME\Retail\Models\Unit;
use ME\Retail\Models\Warehouse;
use ME\Retail\Services\StockTransferService;

class StockTransferController extends Controller
{
    public function __construct(protected StockTransferService $stockTransferService)
    {
        $this->middleware('authorization:retail_inventory.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_inventory.transfer')->only(['create', 'store']);
    }

    public function index()
    {
        $transfers = StockTransfer::with(['fromWarehouse', 'toWarehouse'])->latest()->paginate(get_setting('pagination', 10));
        return view('retail::stock-transfers.index', compact('transfers'));
    }

    public function create()
    {
        $warehouses = Warehouse::where('status', true)->get();
        // Excludes template products that have generated variants — only the variants themselves hold stock.
        $products   = Product::with('unit')->where('status', true)->whereDoesntHave('variants')->get();
        $units      = Unit::where('status', true)->get();
        $batches    = Batch::where('status', true)->where('quantity', '>', 0)->get();
        $serials    = Serial::where('status', SerialStatus::Available)->get();

        return view('retail::stock-transfers.create', compact('warehouses', 'products', 'units', 'batches', 'serials'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_warehouse_id'          => 'required|exists:retail_warehouses,id',
            'to_warehouse_id'            => 'required|different:from_warehouse_id|exists:retail_warehouses,id',
            'transfer_date'              => 'required|date',
            'notes'                      => 'nullable|string',
            'items'                      => 'required|array|min:1',
            'items.*.product_id'        => 'required|exists:retail_products,id',
            'items.*.unit_id'           => 'required|exists:retail_units,id',
            'items.*.quantity'          => 'required|numeric|min:0.0001',
            'items.*.source_batch_id'  => 'nullable|exists:retail_batches,id',
            'items.*.serial_ids'       => 'nullable|array',
            'items.*.serial_ids.*'     => 'nullable|exists:retail_serials,id',
        ]);

        DB::beginTransaction();
        try {
            $transfer = $this->stockTransferService->create($request->all(), auth()->id());
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.stock-transfers.create')->with('error', $e->getMessage() ?: __('Failed to create stock transfer'));
        }

        return redirect()->route('retail.stock-transfers.show', $transfer->id)->with('success', __('Stock transfer completed successfully'));
    }

    public function show(StockTransfer $stockTransfer)
    {
        $stockTransfer->load(['fromWarehouse', 'toWarehouse', 'items.product', 'items.unit', 'items.sourceBatch', 'items.destinationBatch']);
        return view('retail::stock-transfers.show', compact('stockTransfer'));
    }
}
