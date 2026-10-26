<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\EmiInstallment;
use ME\Retail\Services\EmiInstallmentService;

class EmiInstallmentController extends Controller
{
    public function __construct(protected EmiInstallmentService $emiInstallmentService)
    {
        $this->middleware('authorization:retail_emi.pay')->only('pay');
    }

    public function pay(Request $request, EmiInstallment $emiInstallment)
    {
        $request->validate([
            'amount'                    => 'required|numeric|min:0.01',
            'payment_method'            => 'required|string|max:50',
            'cash_register_session_id' => 'nullable|exists:retail_cash_register_sessions,id',
            'notes'                     => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $this->emiInstallmentService->pay(
                $emiInstallment,
                (float) $request->amount,
                $request->payment_method,
                auth()->id(),
                $request->cash_register_session_id,
                $request->notes
            );
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.emi-contracts.show', $emiInstallment->emi_contract_id)->with('error', $e->getMessage() ?: __('Failed to record installment payment'));
        }

        return redirect()->route('retail.emi-contracts.show', $emiInstallment->emi_contract_id)->with('success', __('Installment payment recorded successfully'));
    }
}
