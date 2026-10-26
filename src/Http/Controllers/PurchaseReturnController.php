<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\Purchase;
use ME\Retail\Models\PurchaseReturn;
use ME\Retail\Services\PurchaseReturnService;

class PurchaseReturnController extends Controller
{
    public function __construct(protected PurchaseReturnService $purchaseReturnService)
    {
        $this->middleware('authorization:retail_purchase.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_purchase.return')->only(['create', 'store']);
    }

    public function index()
    {
        $returns = PurchaseReturn::with(['purchase', 'supplier'])->latest()->paginate(get_setting('pagination', 10));
        return view('retail::purchases.returns.index', compact('returns'));
    }

    public function create(Purchase $purchase)
    {
        $purchase->load('items.product', 'items.unit', 'items.batch', 'items.serials');
        return view('retail::purchases.returns.create', compact('purchase'));
    }

    public function store(Request $request, Purchase $purchase)
    {
        $request->validate([
            'items'                  => 'required|array|min:1',
            'items.*.purchase_item_id' => 'required|exists:retail_purchase_items,id',
            'items.*.quantity'       => 'nullable|numeric|min:0',
            'items.*.serial_ids'     => 'nullable|array',
            'items.*.serial_ids.*'   => 'nullable|exists:retail_serials,id',
            'notes'                  => 'nullable|string',
        ]);

        $items = collect($request->items)
            ->filter(fn ($item) => (float) ($item['quantity'] ?? 0) > 0 || !empty($item['serial_ids'] ?? []))
            ->values()
            ->all();

        if (empty($items)) {
            return redirect()->route('retail.purchases.returns.create', $purchase->id)->with('error', __('Enter a quantity to return for at least one item'));
        }

        DB::beginTransaction();
        try {
            $purchaseReturn = $this->purchaseReturnService->create($purchase, $items, $request->notes, auth()->id());
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.purchases.returns.create', $purchase->id)->with('error', $e->getMessage() ?: __('Failed to process return'));
        }

        return redirect()->route('retail.purchases.returns.show', $purchaseReturn->id)->with('success', __('Purchase return processed successfully'));
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['purchase', 'supplier', 'warehouse', 'items.product', 'items.serial']);
        return view('retail::purchases.returns.show', compact('purchaseReturn'));
    }
}
