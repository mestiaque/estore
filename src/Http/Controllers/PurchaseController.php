<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Enums\CashRegisterSessionStatus;
use ME\Retail\Models\CashRegisterSession;
use ME\Retail\Models\Product;
use ME\Retail\Models\Purchase;
use ME\Retail\Models\Store;
use ME\Retail\Models\Supplier;
use ME\Retail\Models\Unit;
use ME\Retail\Models\Warehouse;
use ME\Retail\Services\PurchaseService;

class PurchaseController extends Controller
{
    public function __construct(protected PurchaseService $purchaseService)
    {
        $this->middleware('authorization:retail_purchase.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_purchase.create')->only(['create', 'store']);
    }

    public function index()
    {
        $purchases = Purchase::with(['supplier', 'store', 'warehouse'])->latest()->paginate(get_setting('pagination', 10));
        return view('retail::purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers  = Supplier::where('status', true)->get();
        $stores     = Store::where('status', true)->get();
        $warehouses = Warehouse::where('status', true)->get();
        // Excludes template products that have generated variants — only the variants themselves are purchasable.
        $products   = Product::with('unit')->where('status', true)->whereDoesntHave('variants')->get();
        $units      = Unit::where('status', true)->get();
        $openSessions = CashRegisterSession::with('cashRegister')->where('status', CashRegisterSessionStatus::Open)->get();

        return view('retail::purchases.create', compact('suppliers', 'stores', 'warehouses', 'products', 'units', 'openSessions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'               => 'required|exists:retail_suppliers,id',
            'store_id'                  => 'required|exists:retail_stores,id',
            'warehouse_id'               => 'required|exists:retail_warehouses,id',
            'reference_number'          => 'nullable|string|max:255',
            'purchase_date'             => 'required|date',
            'discount'                  => 'nullable|numeric|min:0',
            'tax'                       => 'nullable|numeric|min:0',
            'paid_amount'               => 'nullable|numeric|min:0',
            'payment_method'            => 'nullable|string|max:50',
            'cash_register_session_id' => 'nullable|exists:retail_cash_register_sessions,id',
            'notes'                     => 'nullable|string',
            'items'                     => 'required|array|min:1',
            'items.*.product_id'       => 'required|exists:retail_products,id',
            'items.*.unit_id'          => 'required|exists:retail_units,id',
            'items.*.quantity'         => 'required|numeric|min:0.0001',
            'items.*.purchase_price'   => 'required|numeric|min:0',
            'items.*.discount'         => 'nullable|numeric|min:0',
            'items.*.tax'              => 'nullable|numeric|min:0',
            'items.*.batch_number'    => 'nullable|string|max:100',
            'items.*.expiry_date'     => 'nullable|date',
            'items.*.serial_numbers'  => 'nullable|array',
            'items.*.serial_numbers.*' => 'nullable|string|max:255',
            'items.*.warranty_months' => 'nullable|integer|min:0|max:600',
        ]);

        DB::beginTransaction();
        try {
            $purchase = $this->purchaseService->create($request->all(), auth()->id());
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.purchases.create')->with('error', $e->getMessage() ?: __('Failed to create purchase'));
        }

        return redirect()->route('retail.purchases.show', $purchase->id)->with('success', __('Purchase recorded successfully'));
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'store', 'warehouse', 'items.product', 'items.unit', 'items.batch', 'items.serials', 'payments']);
        $openSessions = CashRegisterSession::with('cashRegister')->where('status', CashRegisterSessionStatus::Open)->get();
        return view('retail::purchases.show', compact('purchase', 'openSessions'));
    }
}
