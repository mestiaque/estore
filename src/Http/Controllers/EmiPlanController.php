<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Enums\EmiFeeType;
use ME\Retail\Enums\EmiInterestType;
use ME\Retail\Models\EmiBank;
use ME\Retail\Models\EmiPlan;

class EmiPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_emi_plan.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_emi_plan.create')->only(['create', 'store']);
        $this->middleware('authorization:retail_emi_plan.edit')->only(['edit', 'update']);
        $this->middleware('authorization:retail_emi_plan.delete')->only('destroy');
    }

    public function index()
    {
        $plans = EmiPlan::with('bank')->latest()->paginate(get_setting('pagination', 10));
        return view('retail::emi-plans.index', compact('plans'));
    }

    public function create()
    {
        $banks = EmiBank::where('status', true)->get();
        return view('retail::emi-plans.create', compact('banks'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DB::beginTransaction();
        try {
            EmiPlan::create($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.emi-plans.index')->with('error', __('Failed to create plan'));
        }

        return redirect()->route('retail.emi-plans.index')->with('success', __('EMI plan created successfully'));
    }

    public function show(EmiPlan $emiPlan)
    {
        $emiPlan->load('bank');
        return view('retail::emi-plans.show', ['plan' => $emiPlan]);
    }

    public function edit(EmiPlan $emiPlan)
    {
        $banks = EmiBank::where('status', true)->get();
        return view('retail::emi-plans.edit', ['plan' => $emiPlan, 'banks' => $banks]);
    }

    public function update(Request $request, EmiPlan $emiPlan)
    {
        $data = $this->validateData($request);

        DB::beginTransaction();
        try {
            $emiPlan->update($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.emi-plans.index')->with('error', __('Failed to update plan'));
        }

        return redirect()->route('retail.emi-plans.index')->with('success', __('EMI plan updated successfully'));
    }

    public function destroy(EmiPlan $emiPlan)
    {
        DB::beginTransaction();
        try {
            $emiPlan->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.emi-plans.index')->with('error', __('Failed to delete plan'));
        }

        return redirect()->route('retail.emi-plans.index')->with('success', __('EMI plan deleted successfully'));
    }

    private function validateData(Request $request): array
    {
        $request->validate([
            'emi_bank_id'       => 'required|exists:retail_emi_banks,id',
            'name'              => 'required|string|max:255',
            'duration_months'   => 'required|integer|min:1|max:60',
            'interest_type'     => 'required|in:' . implode(',', array_column(EmiInterestType::cases(), 'value')),
            'interest_rate'     => 'required|numeric|min:0',
            'fee_type'          => 'required|in:' . implode(',', array_column(EmiFeeType::cases(), 'value')),
            'fee_value'         => 'required|numeric|min:0',
            'min_amount'        => 'nullable|numeric|min:0',
            'max_amount'        => 'nullable|numeric|min:0',
            'status'            => 'nullable|boolean',
        ]);

        return [
            'emi_bank_id'      => $request->emi_bank_id,
            'name'             => $request->name,
            'duration_months'  => $request->duration_months,
            'interest_type'    => $request->interest_type,
            'interest_rate'    => $request->interest_rate,
            'fee_type'         => $request->fee_type,
            'fee_value'        => $request->fee_value,
            'min_amount'       => $request->min_amount ?: 0,
            'max_amount'       => $request->max_amount ?: null,
            'status'           => $request->has('status') ? 1 : 0,
        ];
    }
}
