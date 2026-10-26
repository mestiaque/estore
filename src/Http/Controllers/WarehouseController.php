<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\Store;
use ME\Retail\Models\Warehouse;

class WarehouseController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_warehouse.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_warehouse.create')->only(['create', 'store']);
        $this->middleware('authorization:retail_warehouse.edit')->only(['edit', 'update']);
        $this->middleware('authorization:retail_warehouse.delete')->only('destroy');
    }

    public function index()
    {
        $warehouses = Warehouse::with('store')->latest()->paginate(get_setting('pagination', 10));
        return view('retail::warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        $stores = Store::where('status', true)->get();
        return view('retail::warehouses.create', compact('stores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:retail_stores,id',
            'name'     => 'required|string|max:255',
            'code'     => 'required|string|max:50|unique:retail_warehouses,code',
            'address'  => 'nullable|string',
            'status'   => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            Warehouse::create([
                'store_id' => $request->store_id,
                'name'     => $request->name,
                'code'     => $request->code,
                'address'  => $request->address,
                'status'   => $request->has('status') ? 1 : 0,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.warehouses.index')->with('error', __('Failed to create warehouse'));
        }

        return redirect()->route('retail.warehouses.index')->with('success', __('Warehouse created successfully'));
    }

    public function show(Warehouse $warehouse)
    {
        $warehouse->load('store');
        return view('retail::warehouses.show', compact('warehouse'));
    }

    public function edit(Warehouse $warehouse)
    {
        $stores = Store::where('status', true)->get();
        return view('retail::warehouses.edit', compact('warehouse', 'stores'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'store_id' => 'required|exists:retail_stores,id',
            'name'     => 'required|string|max:255',
            'code'     => 'required|string|max:50|unique:retail_warehouses,code,' . $warehouse->id,
            'address'  => 'nullable|string',
            'status'   => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $warehouse->update([
                'store_id' => $request->store_id,
                'name'     => $request->name,
                'code'     => $request->code,
                'address'  => $request->address,
                'status'   => $request->has('status') ? 1 : 0,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.warehouses.index')->with('error', __('Failed to update warehouse'));
        }

        return redirect()->route('retail.warehouses.index')->with('success', __('Warehouse updated successfully'));
    }

    public function destroy(Warehouse $warehouse)
    {
        DB::beginTransaction();
        try {
            $warehouse->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.warehouses.index')->with('error', __('Failed to delete warehouse'));
        }

        return redirect()->route('retail.warehouses.index')->with('success', __('Warehouse deleted successfully'));
    }
}
