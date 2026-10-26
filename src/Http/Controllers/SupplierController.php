<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\Supplier;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_supplier.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_supplier.create')->only(['create', 'store']);
        $this->middleware('authorization:retail_supplier.edit')->only(['edit', 'update']);
        $this->middleware('authorization:retail_supplier.delete')->only('destroy');
    }

    public function index()
    {
        $suppliers = Supplier::latest()->paginate(get_setting('pagination', 10));
        return view('retail::suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('retail::suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DB::beginTransaction();
        try {
            Supplier::create($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.suppliers.index')->with('error', __('Failed to create supplier'));
        }

        return redirect()->route('retail.suppliers.index')->with('success', __('Supplier created successfully'));
    }

    public function show(Supplier $supplier)
    {
        $transactions = $supplier->transactions()->latest('id')->paginate(get_setting('pagination', 10));
        return view('retail::suppliers.show', compact('supplier', 'transactions'));
    }

    public function edit(Supplier $supplier)
    {
        return view('retail::suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $this->validateData($request, $supplier->id);

        DB::beginTransaction();
        try {
            $supplier->update($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.suppliers.index')->with('error', __('Failed to update supplier'));
        }

        return redirect()->route('retail.suppliers.index')->with('success', __('Supplier updated successfully'));
    }

    public function destroy(Supplier $supplier)
    {
        DB::beginTransaction();
        try {
            $supplier->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.suppliers.index')->with('error', __('Failed to delete supplier'));
        }

        return redirect()->route('retail.suppliers.index')->with('success', __('Supplier deleted successfully'));
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'company'         => 'nullable|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:255',
            'address'         => 'nullable|string',
            'tax_number'      => 'nullable|string|max:100',
            'opening_balance' => 'nullable|numeric',
            'credit_limit'    => 'nullable|numeric|min:0',
            'status'          => 'nullable|boolean',
            'notes'           => 'nullable|string',
        ]);

        return [
            'name'            => $request->name,
            'company'         => $request->company,
            'phone'           => $request->phone,
            'email'           => $request->email,
            'address'         => $request->address,
            'tax_number'      => $request->tax_number,
            'opening_balance' => $request->opening_balance ?: 0,
            'credit_limit'    => $request->credit_limit,
            'status'          => $request->has('status') ? 1 : 0,
            'notes'           => $request->notes,
        ];
    }
}
