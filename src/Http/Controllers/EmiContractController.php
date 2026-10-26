<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\EmiBank;
use ME\Retail\Models\EmiContract;
use ME\Retail\Models\EmiPlan;
use ME\Retail\Models\Sale;
use ME\Retail\Services\EmiContractService;

class EmiContractController extends Controller
{
    public function __construct(protected EmiContractService $emiContractService)
    {
        $this->middleware('authorization:retail_emi.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_emi.create')->only(['create', 'store']);
    }

    public function index()
    {
        $contracts = EmiContract::with(['sale', 'customer', 'bank', 'plan'])->latest()->paginate(get_setting('pagination', 10));
        return view('retail::emi-contracts.index', compact('contracts'));
    }

    public function create(Sale $sale)
    {
        $banks = EmiBank::with(['plans' => fn ($q) => $q->where('status', true)])->where('status', true)->get();

        return view('retail::emi-contracts.create', compact('sale', 'banks'));
    }

    public function store(Request $request, Sale $sale)
    {
        $request->validate([
            'emi_bank_id'  => 'required|exists:retail_emi_banks,id',
            'emi_plan_id'  => 'required|exists:retail_emi_plans,id',
            'down_payment' => 'nullable|numeric|min:0',
            'start_date'   => 'required|date',
        ]);

        $bank = EmiBank::findOrFail($request->emi_bank_id);
        $plan = EmiPlan::findOrFail($request->emi_plan_id);

        DB::beginTransaction();
        try {
            $contract = $this->emiContractService->create($sale, $bank, $plan, (float) ($request->down_payment ?: 0), $request->start_date, auth()->id());
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.emi-contracts.create', $sale->id)->with('error', $e->getMessage() ?: __('Failed to create EMI contract'));
        }

        return redirect()->route('retail.emi-contracts.show', $contract->id)->with('success', __('EMI contract created successfully'));
    }

    public function show(EmiContract $emiContract)
    {
        $emiContract->load(['sale', 'customer', 'bank', 'plan', 'installments.payments']);
        return view('retail::emi-contracts.show', ['contract' => $emiContract]);
    }
}
