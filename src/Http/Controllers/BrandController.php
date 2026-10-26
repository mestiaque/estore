<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\Brand;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_brand.view')->only(['index', 'show']);
        $this->middleware('authorization:retail_brand.create')->only(['create', 'store']);
        $this->middleware('authorization:retail_brand.edit')->only(['edit', 'update']);
        $this->middleware('authorization:retail_brand.delete')->only('destroy');
    }

    public function index()
    {
        $brands = Brand::latest()->paginate(get_setting('pagination', 10));
        return view('retail::brands.index', compact('brands'));
    }

    public function create()
    {
        return view('retail::brands.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            Brand::create([
                'name'        => $request->name,
                'slug'        => Str::slug($request->name) . '-' . Str::random(6),
                'description' => $request->description,
                'status'      => $request->has('status') ? 1 : 0,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.brands.index')->with('error', __('Failed to create brand'));
        }

        return redirect()->route('retail.brands.index')->with('success', __('Brand created successfully'));
    }

    public function show(Brand $brand)
    {
        return view('retail::brands.show', compact('brand'));
    }

    public function edit(Brand $brand)
    {
        return view('retail::brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $brand->update([
                'name'        => $request->name,
                'description' => $request->description,
                'status'      => $request->has('status') ? 1 : 0,
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.brands.index')->with('error', __('Failed to update brand'));
        }

        return redirect()->route('retail.brands.index')->with('success', __('Brand updated successfully'));
    }

    public function destroy(Brand $brand)
    {
        DB::beginTransaction();
        try {
            $brand->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('retail.brands.index')->with('error', __('Failed to delete brand'));
        }

        return redirect()->route('retail.brands.index')->with('success', __('Brand deleted successfully'));
    }
}
