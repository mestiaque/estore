<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Enums\CashRegisterSessionStatus;
use ME\Retail\Models\CashRegisterSession;
use ME\Retail\Models\Sale;
use ME\Retail\Models\SaleReturn;
use ME\Retail\Services\SaleReturnService;

class SaleReturnController extends Controller
{
    public function __construct(protected SaleReturnService $saleReturnService)
    {
        $this->middleware('authorization:retail_sale.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_sale.return')->only(['create', 'store']);
    }

    public function index()
    {
        $returns = SaleReturn::with(['sale', 'customer'])->latest()->paginate(get_setting('pagination', 10));
        return view('retail::sales.returns.index', compact('returns'));
    }

    public function create(Sale $sale)
    {
        $sale->load('items.product', 'items.unit', 'items.batch', 'items.serials');
        $openSessions = CashRegisterSession::with('cashRegister')->where('status', CashRegisterSessionStatus::Open)->get();
        return view('retail::sales.returns.create', compact('sale', 'openSessions'));
    }

    public function store(Request $request, Sale $sale)
    {
        $request->validate([
            'items'                     => 'required|array|min:1',
            'items.*.sale_item_id'    => 'required|exists:retail_sale_items,id',
            'items.*.quantity'        => 'nullable|numeric|min:0',
            'items.*.serial_ids'      => 'nullable|array',
            'items.*.serial_ids.*'    => 'nullable|exists:retail_serials,id',
            'cash_register_session_id' => 'nullable|exists:retail_cash_register_sessions,id',
            'notes'                     => 'nullable|string',
        ]);

        $items = collect($request->items)
            ->filter(fn ($item) => (float) ($item['quantity'] ?? 0) > 0 || !empty($item['serial_ids'] ?? []))
            ->values()
            ->all();

        if (empty($items)) {
            return redirect()->route('retail.sales.returns.create', $sale->id)->with('error', __('Enter a quantity to return for at least one item'));
        }

        DB::beginTransaction();
        try {
            $saleReturn = $this->saleReturnService->create($sale, $items, $request->notes, auth()->id(), $request->cash_register_session_id);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.sales.returns.create', $sale->id)->with('error', $e->getMessage() ?: __('Failed to process return'));
        }

        return redirect()->route('retail.sales.returns.show', $saleReturn->id)->with('success', __('Sale return processed successfully'));
    }

    public function show(SaleReturn $saleReturn)
    {
        $saleReturn->load(['sale', 'customer', 'warehouse', 'items.product']);
        return view('retail::sales.returns.show', compact('saleReturn'));
    }
}
