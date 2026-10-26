<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\Customer;
use ME\Retail\Services\LoyaltyService;

class LoyaltyAdjustmentController extends Controller
{
    public function __construct(protected LoyaltyService $loyaltyService)
    {
        $this->middleware('authorization:retail_loyalty.view')->only(['create']);
        $this->middleware('authorization:retail_loyalty.adjust')->only('store');
    }

    public function create()
    {
        $customers = Customer::where('status', true)->get();
        return view('retail::loyalty.adjust', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:retail_customers,id',
            'points'      => 'required|numeric|not_in:0',
            'reason'      => 'required|string|max:255',
        ]);

        $customer = Customer::findOrFail($request->customer_id);

        DB::beginTransaction();
        try {
            $this->loyaltyService->adjust($customer, (float) $request->points, $request->reason, auth()->id());
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.customers.show', $customer->id)->with('error', $e->getMessage() ?: __('Failed to adjust points'));
        }

        return redirect()->route('retail.customers.show', $customer->id)->with('success', __('Points adjusted successfully'));
    }
}
