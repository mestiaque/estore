<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\Unit;

class UnitController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_unit.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_unit.create')->only(['create', 'store']);
        $this->middleware('authorization:retail_unit.edit')->only(['edit', 'update']);
        $this->middleware('authorization:retail_unit.delete')->only('destroy');
    }

    public function index()
    {
        $units = Unit::with('baseUnit')->latest()->paginate(get_setting('pagination', 10));
        return view('retail::units.index', compact('units'));
    }

    public function create()
    {
        $units = Unit::whereNull('base_unit_id')->get();
        return view('retail::units.create', compact('units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'base_unit_id'      => 'nullable|exists:retail_units,id',
            'name'              => 'required|string|max:255',
            'short_name'        => 'required|string|max:20',
            'conversion_factor' => 'required|numeric|min:0.0001',
            'status'            => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            Unit::create([
                'base_unit_id'      => $request->base_unit_id,
                'name'              => $request->name,
                'short_name'        => $request->short_name,
                'conversion_factor' => $request->base_unit_id ? $request->conversion_factor : 1,
                'status'            => $request->has('status') ? 1 : 0,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.units.index')->with('error', __('Failed to create unit'));
        }

        return redirect()->route('retail.units.index')->with('success', __('Unit created successfully'));
    }

    public function show(Unit $unit)
    {
        return view('retail::units.show', compact('unit'));
    }

    public function edit(Unit $unit)
    {
        $units = Unit::whereNull('base_unit_id')->where('id', '!=', $unit->id)->get();
        return view('retail::units.edit', compact('unit', 'units'));
    }

    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'base_unit_id'      => 'nullable|exists:retail_units,id|different:id',
            'name'              => 'required|string|max:255',
            'short_name'        => 'required|string|max:20',
            'conversion_factor' => 'required|numeric|min:0.0001',
            'status'            => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $unit->update([
                'base_unit_id'      => $request->base_unit_id,
                'name'              => $request->name,
                'short_name'        => $request->short_name,
                'conversion_factor' => $request->base_unit_id ? $request->conversion_factor : 1,
                'status'            => $request->has('status') ? 1 : 0,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.units.index')->with('error', __('Failed to update unit'));
        }

        return redirect()->route('retail.units.index')->with('success', __('Unit updated successfully'));
    }

    public function destroy(Unit $unit)
    {
        DB::beginTransaction();
        try {
            $unit->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.units.index')->with('error', __('Failed to delete unit'));
        }

        return redirect()->route('retail.units.index')->with('success', __('Unit deleted successfully'));
    }
}
