<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\Purchase;
use ME\Retail\Services\PurchaseService;

class PurchasePaymentController extends Controller
{
    public function __construct(protected PurchaseService $purchaseService)
    {
        $this->middleware('authorization:retail_purchase.pay')->only('store');
    }

    public function store(Request $request, Purchase $purchase)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'cash_register_session_id' => 'nullable|exists:retail_cash_register_sessions,id',
            'reference'      => 'nullable|string|max:255',
            'notes'          => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $this->purchaseService->addPayment(
                $purchase,
                (float) $request->amount,
                $request->payment_method,
                $request->reference,
                $request->notes,
                auth()->id(),
                $request->cash_register_session_id
            );
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.purchases.show', $purchase->id)->with('error', $e->getMessage() ?: __('Failed to record payment'));
        }

        return redirect()->route('retail.purchases.show', $purchase->id)->with('success', __('Payment recorded successfully'));
    }
}
