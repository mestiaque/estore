<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Enums\CashRegisterSessionStatus;
use ME\Retail\Enums\SerialStatus;
use ME\Retail\Models\Batch;
use ME\Retail\Models\CashRegisterSession;
use ME\Retail\Models\Category;
use ME\Retail\Models\Customer;
use ME\Retail\Models\HeldSale;
use ME\Retail\Models\Product;
use ME\Retail\Models\ProductStock;
use ME\Retail\Models\Sale;
use ME\Retail\Models\Serial;
use ME\Retail\Models\Store;
use ME\Retail\Models\Unit;
use ME\Retail\Models\Warehouse;
use ME\Retail\Services\SaleService;
use ME\Retail\Support\RetailModules;
use ME\Retail\Support\RetailSettings;

class SaleController extends Controller
{
    public function __construct(protected SaleService $saleService)
    {
        $this->middleware('authorization:retail_sale.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_sale.create')->only(['create', 'store']);
    }

    public function index()
    {
        $sales = Sale::with(['customer', 'store', 'warehouse'])->latest()->paginate(get_setting('pagination', 10));
        return view('retail::sales.index', compact('sales'));
    }

    public function create(Request $request)
    {
        $customers  = Customer::with(['membership', 'level'])->where('status', true)->orderBy('name')->get();
        $stores     = Store::where('status', true)->get();
        $warehouses = Warehouse::where('status', true)->get();
        $categories = Category::where('status', true)->orderBy('name')->get(['id', 'name']);
        // Excludes template products that have generated variants — only the variants themselves are sellable.
        $products   = Product::with('unit')->where('status', true)->whereDoesntHave('variants')->orderBy('name')->get();
        $units      = Unit::where('status', true)->get();
        $batches    = Batch::where('status', true)->where('quantity', '>', 0)->get();
        $serials    = Serial::where('status', SerialStatus::Available)->get();
        // Per-warehouse on-hand quantity, shown on the POS product tiles.
        $stocks     = ProductStock::query()->where('quantity', '!=', 0)->get(['product_id', 'warehouse_id', 'quantity']);
        $openSessions = RetailModules::enabled('cash_register')
            ? CashRegisterSession::with('cashRegister')->where('status', CashRegisterSessionStatus::Open)->get()
            : collect();
        $heldSales    = RetailModules::enabled('hold_sale') ? HeldSale::with(['customer', 'user'])->latest()->get() : collect();
        // ?resume={id} reopens a held cart in this form; it is deleted once the sale completes.
        $resumedSale  = RetailModules::enabled('hold_sale') && $request->filled('resume') ? HeldSale::find($request->integer('resume')) : null;
        $pointValue   = (float) RetailSettings::get('loyalty_point_value');

        return view('retail::sales.create', compact(
            'customers', 'stores', 'warehouses', 'categories', 'products', 'units', 'batches', 'serials', 'stocks',
            'openSessions', 'heldSales', 'resumedSale', 'pointValue'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'              => 'nullable|exists:retail_customers,id',
            'store_id'                 => 'required|exists:retail_stores,id',
            'warehouse_id'             => 'required|exists:retail_warehouses,id',
            'sale_date'                => 'required|date',
            'discount'                 => 'nullable|numeric|min:0',
            'tax'                      => 'nullable|numeric|min:0',
            'redeem_points'            => 'nullable|numeric|min:0',
            'coupon_code'              => 'nullable|string|max:100',
            'cash_register_session_id' => 'nullable|exists:retail_cash_register_sessions,id',
            'notes'                    => 'nullable|string',
            'items'                    => 'required|array|min:1',
            'items.*.product_id'      => 'required|exists:retail_products,id',
            'items.*.unit_id'         => 'required|exists:retail_units,id',
            'items.*.quantity'        => 'required|numeric|min:0.0001',
            'items.*.sale_price'      => 'required|numeric|min:0',
            'items.*.discount'        => 'nullable|numeric|min:0',
            'items.*.tax'             => 'nullable|numeric|min:0',
            'items.*.batch_id'        => 'nullable|exists:retail_batches,id',
            'items.*.serial_ids'      => 'nullable|array',
            'items.*.serial_ids.*'    => 'nullable|exists:retail_serials,id',
            'payments'                 => 'nullable|array',
            'payments.*.payment_method' => 'required_with:payments|string|max:50',
            'payments.*.amount'        => 'required_with:payments|numeric|min:0.01',
            'payments.*.reference'     => 'nullable|string|max:255',
            'held_sale_id'             => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $sale = $this->saleService->create($request->all(), auth()->id());
            if ($request->filled('held_sale_id')) {
                HeldSale::whereKey($request->integer('held_sale_id'))->delete();
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.sales.create', array_filter(['resume' => $request->held_sale_id]))->with('error', $e->getMessage() ?: __('Failed to create sale'));
        }

        return redirect()->route('retail.sales.show', $sale->id)->with('success', __('Sale completed successfully'));
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'store', 'warehouse', 'items.product', 'items.unit', 'items.batch', 'items.serials', 'items.discountRule', 'payments', 'couponRule', 'emiContract']);
        return view('retail::sales.show', compact('sale'));
    }
}
