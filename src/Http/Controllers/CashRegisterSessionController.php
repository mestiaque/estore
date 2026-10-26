<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\CashRegister;
use ME\Retail\Models\CashRegisterSession;
use ME\Retail\Services\CashRegisterService;

class CashRegisterSessionController extends Controller
{
    public function __construct(protected CashRegisterService $cashRegisterService)
    {
        $this->middleware('authorization:retail_cash_register_session.view')->only('show');
        $this->middleware('authorization:retail_cash_register_session.open')->only(['openForm', 'open']);
        $this->middleware('authorization:retail_cash_register_session.close')->only(['closeForm', 'close']);
    }

    public function openForm(CashRegister $cashRegister)
    {
        return view('retail::cash-registers.open', ['register' => $cashRegister]);
    }

    public function open(Request $request, CashRegister $cashRegister)
    {
        $request->validate([
            'opening_balance' => 'required|numeric|min:0',
            'notes'           => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $session = $this->cashRegisterService->openSession($cashRegister, (float) $request->opening_balance, $request->notes, auth()->id());
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.cash-registers.show', $cashRegister->id)->with('error', $e->getMessage() ?: __('Failed to open register'));
        }

        return redirect()->route('retail.cash-register-sessions.show', $session->id)->with('success', __('Register opened successfully'));
    }

    public function closeForm(CashRegisterSession $cashRegisterSession)
    {
        $cashRegisterSession->load('cashRegister');
        return view('retail::cash-registers.close', ['session' => $cashRegisterSession]);
    }

    public function close(Request $request, CashRegisterSession $cashRegisterSession)
    {
        $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'notes'           => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $this->cashRegisterService->closeSession($cashRegisterSession, (float) $request->closing_balance, $request->notes, auth()->id());
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.cash-register-sessions.show', $cashRegisterSession->id)->with('error', $e->getMessage() ?: __('Failed to close register'));
        }

        return redirect()->route('retail.cash-register-sessions.show', $cashRegisterSession->id)->with('success', __('Register closed successfully'));
    }

    public function show(CashRegisterSession $cashRegisterSession)
    {
        $cashRegisterSession->load(['cashRegister', 'openedBy', 'closedBy']);
        $transactions = $cashRegisterSession->transactions()->latest('id')->paginate(get_setting('pagination', 10));

        return view('retail::cash-registers.session', ['session' => $cashRegisterSession, 'transactions' => $transactions]);
    }
}
