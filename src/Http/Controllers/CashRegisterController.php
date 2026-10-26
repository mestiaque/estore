<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\CashRegister;
use ME\Retail\Models\Store;

class CashRegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_cash_register.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_cash_register.create')->only(['create', 'store']);
        $this->middleware('authorization:retail_cash_register.edit')->only(['edit', 'update']);
        $this->middleware('authorization:retail_cash_register.delete')->only('destroy');
    }

    public function index()
    {
        $registers = CashRegister::with(['store', 'openSession'])->latest()->paginate(get_setting('pagination', 10));
        return view('retail::cash-registers.index', compact('registers'));
    }

    public function create()
    {
        $stores = Store::where('status', true)->get();
        return view('retail::cash-registers.create', compact('stores'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DB::beginTransaction();
        try {
            CashRegister::create($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.cash-registers.index')->with('error', __('Failed to create cash register'));
        }

        return redirect()->route('retail.cash-registers.index')->with('success', __('Cash register created successfully'));
    }

    public function show(CashRegister $cashRegister)
    {
        $sessions = $cashRegister->sessions()->latest()->paginate(get_setting('pagination', 10));
        return view('retail::cash-registers.show', ['register' => $cashRegister, 'sessions' => $sessions]);
    }

    public function edit(CashRegister $cashRegister)
    {
        $stores = Store::where('status', true)->get();
        return view('retail::cash-registers.edit', ['register' => $cashRegister, 'stores' => $stores]);
    }

    public function update(Request $request, CashRegister $cashRegister)
    {
        $data = $this->validateData($request, $cashRegister->id);

        DB::beginTransaction();
        try {
            $cashRegister->update($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.cash-registers.index')->with('error', __('Failed to update cash register'));
        }

        return redirect()->route('retail.cash-registers.index')->with('success', __('Cash register updated successfully'));
    }

    public function destroy(CashRegister $cashRegister)
    {
        DB::beginTransaction();
        try {
            $cashRegister->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.cash-registers.index')->with('error', __('Failed to delete cash register'));
        }

        return redirect()->route('retail.cash-registers.index')->with('success', __('Cash register deleted successfully'));
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        $request->validate([
            'store_id' => 'required|exists:retail_stores,id',
            'name'     => 'required|string|max:255',
            'code'     => 'required|string|max:50|unique:retail_cash_registers,code,' . $id,
            'status'   => 'nullable|boolean',
        ]);

        return [
            'store_id' => $request->store_id,
            'name'     => $request->name,
            'code'     => $request->code,
            'status'   => $request->has('status') ? 1 : 0,
        ];
    }
}
