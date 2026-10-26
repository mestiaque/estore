<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Enums\StockAdjustmentReason;
use ME\Retail\Enums\StockMovementType;
use ME\Retail\Exceptions\InsufficientStockException;
use ME\Retail\Models\Product;
use ME\Retail\Models\StockAdjustment;
use ME\Retail\Models\Warehouse;
use ME\Retail\Services\StockService;

class StockAdjustmentController extends Controller
{
    public function __construct(protected StockService $stockService)
    {
        $this->middleware('authorization:retail_inventory.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_inventory.adjust')->only(['create', 'store']);
    }

    public function index()
    {
        $adjustments = StockAdjustment::with(['product', 'warehouse'])
            ->latest()
            ->paginate(get_setting('pagination', 10));

        return view('retail::inventory.adjustment-index', compact('adjustments'));
    }

    public function create()
    {
        // Excludes template products that have generated variants — only the variants themselves hold stock.
        $products   = Product::where('status', true)->whereDoesntHave('variants')->get();
        $warehouses = Warehouse::where('status', true)->get();
        $reasons    = StockAdjustmentReason::cases();

        return view('retail::inventory.adjustment-create', compact('products', 'warehouses', 'reasons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id'         => 'required|exists:retail_products,id',
            'warehouse_id'       => 'required|exists:retail_warehouses,id',
            'quantity_adjusted'  => 'required|numeric|not_in:0',
            'reason'             => 'required|in:' . implode(',', array_column(StockAdjustmentReason::cases(), 'value')),
            'notes'              => 'nullable|string',
        ]);

        $product   = Product::findOrFail($request->product_id);
        $warehouse = Warehouse::findOrFail($request->warehouse_id);
        $reason    = StockAdjustmentReason::from($request->reason);
        $quantity  = (float) $request->quantity_adjusted;

        DB::beginTransaction();
        try {
            $before = $this->stockService->currentQuantity($product, $warehouse);

            $ledger = $this->stockService->move(
                product: $product,
                warehouse: $warehouse,
                quantity: $quantity,
                type: StockMovementType::Adjustment,
                referenceType: StockAdjustment::class,
                userId: auth()->id(),
                notes: $reason->label() . ($request->notes ? ' - ' . $request->notes : '')
            );

            $adjustment = StockAdjustment::create([
                'product_id'         => $product->id,
                'warehouse_id'       => $warehouse->id,
                'stock_ledger_id'    => $ledger->id,
                'quantity_before'    => $before,
                'quantity_adjusted'  => $quantity,
                'quantity_after'     => $before + $quantity,
                'reason'             => $reason,
                'notes'              => $request->notes,
                'user_id'            => auth()->id(),
            ]);

            $ledger->update([
                'reference_type' => StockAdjustment::class,
                'reference_id'   => $adjustment->id,
            ]);

            DB::commit();
        } catch (InsufficientStockException $e) {
            DB::rollBack();
            return redirect()->route('retail.stock-adjustments.create')->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.stock-adjustments.create')->with('error', __('Failed to adjust stock'));
        }

        return redirect()->route('retail.stock-adjustments.index')->with('success', __('Stock adjusted successfully'));
    }

    public function show(StockAdjustment $stockAdjustment)
    {
        $stockAdjustment->load(['product', 'warehouse', 'ledger']);
        return view('retail::inventory.adjustment-show', ['adjustment' => $stockAdjustment]);
    }
}
