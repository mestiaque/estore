<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\EmiBank;

class EmiBankController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_emi_bank.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_emi_bank.create')->only(['create', 'store']);
        $this->middleware('authorization:retail_emi_bank.edit')->only(['edit', 'update']);
        $this->middleware('authorization:retail_emi_bank.delete')->only('destroy');
    }

    public function index()
    {
        $banks = EmiBank::latest()->paginate(get_setting('pagination', 10));
        return view('retail::emi-banks.index', compact('banks'));
    }

    public function create()
    {
        return view('retail::emi-banks.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DB::beginTransaction();
        try {
            EmiBank::create($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.emi-banks.index')->with('error', __('Failed to create bank'));
        }

        return redirect()->route('retail.emi-banks.index')->with('success', __('Bank created successfully'));
    }

    public function show(EmiBank $emiBank)
    {
        $emiBank->load('plans');
        return view('retail::emi-banks.show', ['bank' => $emiBank]);
    }

    public function edit(EmiBank $emiBank)
    {
        return view('retail::emi-banks.edit', ['bank' => $emiBank]);
    }

    public function update(Request $request, EmiBank $emiBank)
    {
        $data = $this->validateData($request, $emiBank->id);

        DB::beginTransaction();
        try {
            $emiBank->update($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.emi-banks.index')->with('error', __('Failed to update bank'));
        }

        return redirect()->route('retail.emi-banks.index')->with('success', __('Bank updated successfully'));
    }

    public function destroy(EmiBank $emiBank)
    {
        DB::beginTransaction();
        try {
            $emiBank->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.emi-banks.index')->with('error', __('Failed to delete bank'));
        }

        return redirect()->route('retail.emi-banks.index')->with('success', __('Bank deleted successfully'));
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'code'   => 'required|string|max:50|unique:retail_emi_banks,code,' . $id,
            'status' => 'nullable|boolean',
        ]);

        return [
            'name'   => $request->name,
            'code'   => $request->code,
            'status' => $request->has('status') ? 1 : 0,
        ];
    }
}
