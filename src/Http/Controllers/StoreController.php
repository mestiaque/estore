<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\Store;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_store.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_store.create')->only(['create', 'store']);
        $this->middleware('authorization:retail_store.edit')->only(['edit', 'update']);
        $this->middleware('authorization:retail_store.delete')->only('destroy');
    }

    public function index()
    {
        $stores = Store::latest()->paginate(get_setting('pagination', 10));
        return view('retail::stores.index', compact('stores'));
    }

    public function create()
    {
        return view('retail::stores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'code'    => 'required|string|max:50|unique:retail_stores,code',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'status'  => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            Store::create([
                'name'    => $request->name,
                'code'    => $request->code,
                'phone'   => $request->phone,
                'email'   => $request->email,
                'address' => $request->address,
                'status'  => $request->has('status') ? 1 : 0,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.stores.index')->with('error', __('Failed to create store'));
        }

        return redirect()->route('retail.stores.index')->with('success', __('Store created successfully'));
    }

    public function show(Store $store)
    {
        return view('retail::stores.show', compact('store'));
    }

    public function edit(Store $store)
    {
        return view('retail::stores.edit', compact('store'));
    }

    public function update(Request $request, Store $store)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'code'    => 'required|string|max:50|unique:retail_stores,code,' . $store->id,
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'status'  => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $store->update([
                'name'    => $request->name,
                'code'    => $request->code,
                'phone'   => $request->phone,
                'email'   => $request->email,
                'address' => $request->address,
                'status'  => $request->has('status') ? 1 : 0,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.stores.index')->with('error', __('Failed to update store'));
        }

        return redirect()->route('retail.stores.index')->with('success', __('Store updated successfully'));
    }

    public function destroy(Store $store)
    {
        DB::beginTransaction();
        try {
            $store->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.stores.index')->with('error', __('Failed to delete store'));
        }

        return redirect()->route('retail.stores.index')->with('success', __('Store deleted successfully'));
    }
}
